<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Campaign;
use App\Models\CampaignProductStore;
use App\Models\EarlyBirdDiscount;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationController extends Controller
{
    // 一覧
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Reservation::query()->with(['store', 'campaign'])
            ->when($user && $user->role === 'store', fn($q) => $q->where('store_id', $user->store_id))
            ->when($request->filled('store_id'), fn($q) => $q->where('store_id', $request->integer('store_id')))
            ->when($request->filled('campaign_id'), fn($q) => $q->where('campaign_id', $request->integer('campaign_id')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = $request->string('q');
                $q->where(function ($w) use ($s) {
                    $w->where('customer_name', 'like', "%$s%")
                        ->orWhere('phone', 'like', "%$s%");
                });
            })
            ->when($request->filled('from'), fn($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->latest();


        $reservations = $query->paginate(20)->withQueryString();

        return view('reservations.index', [
            'reservations' => $reservations,
            'stores' => Store::orderBy('name')->get(),
            'campaigns' => Campaign::orderBy('start_date', 'desc')->get(),
        ]);
    }

    // 作成フォーム
    public function create(Request $request)
    {
        $user = $request->user();
        return view('reservations.create', [
            'stores' => Store::orderBy('name')->get(),
            'campaigns' => Campaign::where('is_active', true)->orderBy('start_date', 'desc')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'defaultStoreId' => $user?->store_id,
        ]);
    }

    // 詳細
    public function show(Reservation $reservation)
    {
        $reservation->load(['items.product', 'store', 'campaign']);
        return view('reservations.show', compact('reservation'));
    }

    // 登録
    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();
        $items = collect($validated['items']);


// 金額計算＆在庫（残数）チェック
        $total = 0;
        $alerts = [];

        DB::transaction(function () use ($validated, $items, &$total, &$alerts) {
            $reservation = Reservation::create([
                'campaign_id' => $validated['campaign_id'],
                'store_id' => $validated['store_id'],
                'channel' => $validated['channel'],
                'customer_name' => $validated['customer_name'],
                'customer_kana' => $validated['customer_kana'] ?? null,
                'phone' => $validated['phone'],
                'zip' => $validated['zip'] ?? null,
                'address1' => $validated['address1'] ?? null,
                'address2' => $validated['address2'] ?? null,
                'pickup_date' => $validated['pickup_date'] ?? null,
                'pickup_time_slot' => $validated['pickup_time_slot'] ?? null,
                'total_amount' => 0,
                'status' => 'confirmed',
                'notes' => $validated['notes'] ?? null,
            ]);


            foreach ($items as $row) {
                $product = Product::findOrFail($row['product_id']);
                $qty = (int)$row['quantity'];
                $subtotal = $product->price * $qty;
                $total += $subtotal;


                // 残数: planned - reserved を超えないかチェック
                $cps = CampaignProductStore::where([
                    'campaign_id' => $reservation->campaign_id,
                    'product_id' => $product->id,
                    'store_id' => $reservation->store_id,
                ])->lockForUpdate()->first();


                if ($cps) {
                    $remaining = max(0, $cps->planned_quantity - $cps->reserved_quantity);
                    if ($qty > $remaining) {
                        $alerts[] = "『{$product->name}』は残数{$remaining}を超えています（申込数量: {$qty}）。";
                    }
                    // 予約に反映（モック：超過しても登録、ただし警告表示）
                    $cps->increment('reserved_quantity', $qty);
                }


                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'product_id' => $product->id,
                    'unit_price' => $product->price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                ]);
            }


            $reservation->update(['total_amount' => $total]);
        });

        return redirect()
            ->route('reservations.index')
            ->with('status', '予約を登録しました。' . (count($alerts) ? ' 警告: ' . implode(' / ', $alerts) : ''));
    }

    /** AJAX: 単価・割引・小計プレビュー（予約画面の自動反映用） */
    public function pricePreview(Request $r): \Illuminate\Http\JsonResponse
    {
        $data = $r->validate([
            'campaign_id' => ['required','exists:campaigns,id'],
            'store_id'    => ['required','exists:stores,id'],
            'channel'     => ['nullable', \Illuminate\Validation\Rule::in(['store','tokushimaru','web'])],
            'items'       => ['required','array','min:1'],
            'items.*.product_id' => ['required','exists:products,id'],
            'items.*.quantity'   => ['required','integer','min:1'],
        ]);

        $channel = $data['channel'] ?? 'store';
        $lines   = [];
        $totOrig = $totDisc = $totFinal = 0;

        foreach ($data['items'] as $line) {
            $product   = \App\Models\Product::select('id','price')->find($line['product_id']);
            $qty       = (int)$line['quantity'];
            $unitPrice = (int)$product->price;

            $rule = \App\Models\EarlyBirdDiscount::resolveOne(
                (int)$data['campaign_id'],
                (int)$product->id,
                (int)$data['store_id'],
                $channel,
                now()
            );

            $discPer   = $rule ? $rule->calcDiscount($unitPrice) : 0;
            $finalUnit = max(0, $unitPrice - $discPer);

            $lineOrig  = $unitPrice * $qty;
            $lineDisc  = $discPer   * $qty;
            $lineFinal = $finalUnit * $qty;

            $lines[] = [
                'product_id'       => $product->id,
                'unit_price'       => $unitPrice,
                'discount_per_unit'=> $discPer,
                'final_unit'       => $finalUnit,
                'qty'              => $qty,
                'line_discount'    => $lineDisc,
                'line_subtotal'    => $lineFinal,
                'applied'          => (bool)$rule,
                'rule'             => $rule ? [
                    'id'          => $rule->id,
                    'name'        => $rule->name,
                    'display'     => $rule->display_value,
                    'cutoff_date' => optional($rule->cutoff_date)->toDateString(),
                ] : null,
            ];

            $totOrig  += $lineOrig;
            $totDisc  += $lineDisc;
            $totFinal += $lineFinal;
        }

        $rule = \App\Models\EarlyBirdDiscount::resolveOne(
            (int)$data['campaign_id'],
            (int)$product->id,
            (int)$data['store_id'],
            $channel,
            now()
        );

        Log::debug('EB resolve', [
            'campaign_id'=>$data['campaign_id'],
            'product_id' =>$product->id,
            'store_id'   =>$data['store_id'],
            'channel'    =>$channel,
            'rule_id'    =>$rule?->id,
            'rule_name'  =>$rule?->name,
        ]);

        return response()->json([
            'lines'  => $lines,
            'totals' => [
                'original' => $totOrig,
                'discount' => $totDisc,
                'final'    => $totFinal,
            ],
        ]);
    }
}
