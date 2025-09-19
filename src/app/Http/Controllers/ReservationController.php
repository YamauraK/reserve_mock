<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    // 一覧
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Reservation::query()->with(['store', 'campaign'])
            ->when($user && $user->role === UserRole::STORE, fn($q) => $q->where('store_id', $user->store_id))
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

        $storeOptions = Store::orderBy('name')
            ->when($user && $user->role === UserRole::STORE, fn($q) => $q->whereKey($user->store_id))
            ->get();

        return view('reservations.index', [
            'reservations' => $reservations,
            'stores' => $storeOptions,
            'campaigns' => Campaign::orderBy('start_date', 'desc')->get(),
        ]);
    }

    // 作成フォーム
    public function create(Request $request)
    {
        $user = $request->user();
        $stores = Store::orderBy('name')
            ->when($user && $user->role === UserRole::STORE, fn($q) => $q->whereKey($user->store_id))
            ->get();

        $defaultStoreId = $user?->store_id ?? $stores->first()?->id;

        $products = Product::with('stores:id')
            ->where('is_active', true)
            ->when($user && $user->role === UserRole::STORE && $user->store_id, fn($q) => $q->availableForStore($user->store_id))
            ->orderBy('name')
            ->get();

        return view('reservations.create', [
            'stores' => $stores,
            'campaigns' => Campaign::where('is_active', true)->orderBy('start_date', 'desc')->get(),
            'products' => $products,
            'defaultStoreId' => $defaultStoreId,
        ]);
    }

    // 詳細
    public function show(Reservation $reservation)
    {
        $reservation->load(['items.product', 'store', 'campaign']);
        return view('reservations.show', compact('reservation'));
    }

    // 登録
    public function store(Request $request)
    {
        // --- 数量0行を除去（FormRequestのprepareForValidation相当） ---
        $items = collect($request->input('items', []))
            ->filter(fn($item) => (int)($item['quantity'] ?? 0) > 0)
            ->map(fn($item) => [
                'product_id' => (int)($item['product_id'] ?? 0),
                'quantity'   => (int)($item['quantity'] ?? 0),
            ])
            ->values()
            ->all();
        $request->merge(['items' => $items]);
        Log::info('itemsをコレクトした後');

        // --- ルール ---
        $rules = [
            'campaign_id'      => ['required','integer','exists:campaigns,id'],
            'store_id'         => ['required','integer','exists:stores,id'],
            'channel'          => ['required', Rule::in(['store','tokushimaru','web'])],
            'customer_name'    => ['required','string','max:100'],
            'customer_kana'    => ['nullable','string','max:100'],
            'phone'            => ['required','string','max:20'],
            'zip'              => ['nullable','string','max:10'],
            'address1'         => ['nullable','string','max:255'],
            'address2'         => ['nullable','string','max:255'],
            'pickup_date'      => ['nullable','date'],
            'pickup_time_slot' => ['nullable','string','max:50'],
            'notes'            => ['nullable','string','max:1000'],
            'items'                => ['required','array','min:1'],
            'items.*.product_id'   => ['required','integer','min:1'],
            'items.*.quantity'     => ['required','integer','min:1'],
        ];
        $messages = [
            'items.required'       => '商品を1点以上選択してください。',
            'items.min'            => '商品を1点以上選択してください。',
            'items.*.quantity.min' => '数量は1以上で入力してください。',
        ];

        Log::info('$validator前');
        $validator = Validator::make($request->all(), $rules, $messages);
        Log::info('$validatorの後');

        // --- 追加検証（商品存在・店舗適用可否） ---
        $validator->after(function ($v) use ($request) {
            Log::info('X');
            if ($v->errors()->isNotEmpty()) return;

            Log::info('A');
            $storeId = (int)$request->input('store_id');
            $items   = collect($request->input('items', []));
            if (!$storeId || $items->isEmpty()) return;

            Log::info('B');
            $productIds = $items->pluck('product_id')->unique()->filter()->all();
            if (empty($productIds)) return;

            Log::info('C');
            $existingIds = Product::whereIn('id', $productIds)->pluck('id')->all();
            $missingIds  = array_diff($productIds, $existingIds);
            if (!empty($missingIds)) {
                $v->errors()->add('items', '選択された商品が存在しません。');
                return;
            }

            Log::info('D');
            $availableIds = Product::whereIn('id', $productIds)
                ->availableForStore($storeId)
                ->pluck('id')->all();

            $diff = array_diff($productIds, $availableIds);
            Log::info('E');
            if (!empty($diff)) {
                $v->errors()->add('items', '選択された商品はこの店舗では利用できません。');
            }
        });
        Log::info('$validatorアフターの後');

        if ($validator->fails()) {
            Log::info('$validatorがfails');
            // ここに来れば必ず画面上部＆各フィールドにエラーが出る（Bladeに@error済）
            return back()->withErrors($validator)->withInput();
        }

        Log::info('$validatedの前');
        $validated  = $validator->validated();
        Log::info('$itemsColの前');
        $itemsCol   = collect($validated['items']);
        Log::info('$storeIdの前');
        $storeId    = (int)$validated['store_id'];
        Log::info('$productMapの前');
        $productMap = Product::whereIn('id', $itemsCol->pluck('product_id')->unique())
            ->availableForStore($storeId)
            ->get()->keyBy('id');
        Log::info('try前');

        $total  = 0;
        $alerts = [];

        try {
            Log::info('A: before tx');
            DB::transaction(function () use ($validated, $itemsCol, $productMap, &$total, &$alerts) {
                Log::info('B: in tx - before create reservation');
                $reservation = Reservation::create([
                    'campaign_id'      => $validated['campaign_id'],
                    'store_id'         => $validated['store_id'],
                    'channel'          => $validated['channel'],
                    'customer_name'    => $validated['customer_name'],
                    'customer_kana'    => $validated['customer_kana'] ?? null,
                    'phone'            => $validated['phone'],
                    'zip'              => $validated['zip'] ?? null,
                    'address1'         => $validated['address1'] ?? null,
                    'address2'         => $validated['address2'] ?? null,
                    'pickup_date'      => $validated['pickup_date'] ?? null,
                    'pickup_time_slot' => $validated['pickup_time_slot'] ?? null,
                    'total_amount'     => 0,
                    'status'           => 'confirmed',
                    'notes'            => $validated['notes'] ?? null,
                ]);

                foreach ($itemsCol as $row) {
                    Log::info('C: loop start', $row);
                    $product = $productMap->get($row['product_id']);
                    Log::info('D: got product', ['id' => $product?->id]);
                    if (!$product) {
                        // ここで throw すると 500 になって画面が変わらない印象になるので、
                        // 例外は上位で捕捉しフラッシュ表示する。
                        throw new \RuntimeException('選択された商品はこの店舗では利用できません。');
                    }
                    $qty = (int)$row['quantity'];
                    $subtotal = $product->price * $qty;
                    $total += $subtotal;

                    // 残数チェック
                    Log::info('E: before CPS select-for-update');
                    $cps = CampaignProductStore::where([
                        'campaign_id' => $reservation->campaign_id,
                        'product_id'  => $product->id,
                        'store_id'    => $reservation->store_id,
                    ])->lockForUpdate()->first();
                    Log::info('F: after CPS select', ['exists' => (bool)$cps]);

                    if ($cps) {
                        $remaining = max(0, $cps->planned_quantity - $cps->reserved_quantity);
                        if ($qty > $remaining) {
                            $alerts[] = "『{$product->name}』は残数{$remaining}を超えています（申込数量: {$qty}）。";
                        }
                        $cps->increment('reserved_quantity', $qty);
                    }

                    ReservationItem::create([
                        'reservation_id' => $reservation->id,
                        'product_id'     => $product->id,
                        'unit_price'     => $product->price,
                        'quantity'       => $qty,
                        'subtotal'       => $subtotal,
                    ]);
                }
                Log::info('Z: before commit');

                $reservation->update(['total_amount' => $total]);
            });
            Log::info('Z2: after tx');
        } catch (\Throwable $e) {
            // 例外はここで握りつぶさず、ユーザーに表示して戻す
            Log::error('TX ERROR: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            report($e);
            return back()
                ->withInput()
                ->with('error', '登録処理でエラーが発生しました：'.$e->getMessage());
        }

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
            'items.*.product_id' => ['required','integer','min:1'],
            'items.*.quantity'   => ['required','integer','min:1'],
        ]);

        $channel = $data['channel'] ?? 'store';
        $lines   = [];
        $totOrig = $totDisc = $totFinal = 0;

        $availableProducts = Product::whereIn('id', collect($data['items'])->pluck('product_id')->unique())
            ->availableForStore((int)$data['store_id'])
            ->get()
            ->keyBy('id');

        foreach ($data['items'] as $line) {
            $product   = $availableProducts->get($line['product_id']);
            if (!$product) {
                return response()->json(['message' => '選択された商品はこの店舗では利用できません。'], 422);
            }
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

        $rule = null;
        if (isset($product)) {
            $rule = \App\Models\EarlyBirdDiscount::resolveOne(
                (int)$data['campaign_id'],
                (int)$product->id,
                (int)$data['store_id'],
                $channel,
                now()
            );
        }

        if (isset($product)) {
            Log::debug('EB resolve', [
                'campaign_id'=>$data['campaign_id'],
                'product_id' =>$product->id,
                'store_id'   =>$data['store_id'],
                'channel'    =>$channel,
                'rule_id'    =>$rule?->id,
                'rule_name'  =>$rule?->name,
            ]);
        }

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
