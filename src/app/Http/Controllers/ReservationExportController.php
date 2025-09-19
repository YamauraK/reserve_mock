<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationExportController extends Controller
{
    public function index(Request $request): StreamedResponse
    {
        $fileName = 'reservations_' . now()->format('Ymd_His') . '.csv';

        $response = new StreamedResponse(function () use ($request) {
            $handle = fopen('php://output', 'w');

            // ヘッダー行
            fputcsv($handle, [
                'ID', '企画', '店舗', '氏名', '電話', '郵便番号', '住所', '受取日', '受取時間', '金額', '状態', '登録日時'
            ]);

            $query = Reservation::with(['campaign', 'store'])
                ->orderBy('created_at', 'desc');

            $query->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $r->id,
                        $r->campaign?->name,
                        $r->store?->name,
                        $r->customer_name,
                        $r->phone,
                        $r->zip,
                        $r->address1 . ' ' . $r->address2,
                        $r->pickup_date,
                        $r->pickup_time_slot,
                        $r->total_amount,
                        $r->status,
                        $r->created_at->format('Y-m-d H:i')
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        return $response;
    }
}
