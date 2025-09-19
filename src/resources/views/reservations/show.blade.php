@extends('layouts.app')
@section('title','予約詳細')

@section('content')
    @php
        // 状態に応じてバッジ色を割り当て（必要に応じて調整）
        $statusMap = [
          '確定' => 'ok', 'confirmed' => 'ok',
          '保留' => 'warn', 'pending' => 'warn',
          'キャンセル' => 'ng', 'canceled' => 'ng',
        ];
        $statusClass = $statusMap[$reservation->status] ?? 'ok';
    @endphp

    <div class="card">
        <div class="card-body space-y-6">

            {{-- ヘッダ行：ID / 状態 / アクション --}}
            <div class="flex items-start justify-between">
                <div>
                    <div class="form-label">予約ID</div>
                    <div class="text-2xl font-bold">#{{ $reservation->id }}</div>
                </div>
                <div class="text-right">
                    <div class="form-label">状態</div>
                    <span class="badge {{ $statusClass }}">{{ $reservation->status }}</span>
                </div>
            </div>

            {{-- 基本情報 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="form-label">企画</div>
                    <div class="font-semibold">{{ $reservation->campaign->name }}</div>
                </div>
                <div>
                    <div class="form-label">店舗</div>
                    <div class="font-semibold">{{ $reservation->store->name }}</div>
                </div>
                <div>
                    <div class="form-label">受付チャネル</div>
                    <div>{{ $reservation->channel }}</div>
                </div>
            </div>

            {{-- 顧客情報 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="form-label">氏名 / 電話</div>
                    <div class="font-semibold">
                        {{ $reservation->customer_name }}
                        <span class="text-gray-500"> / </span>
                        {{ $reservation->phone }}
                    </div>
                </div>
                <div>
                    <div class="form-label">住所</div>
                    <div>
                        {{ $reservation->zip }}
                        {{ $reservation->address1 }}
                        {{ $reservation->address2 }}
                    </div>
                </div>
            </div>

            {{-- 受取 / 金額 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="form-label">受取</div>
                    <div>
                        {{ $reservation->pickup_date }}
                        @if($reservation->pickup_time_slot)
                            <span class="text-gray-500"> / </span>{{ $reservation->pickup_time_slot }}
                        @endif
                    </div>
                </div>
                <div>
                    <div class="form-label">金額</div>
                    <div class="text-2xl font-bold">¥{{ number_format($reservation->total_amount) }}</div>
                </div>
            </div>

            {{-- 明細テーブル --}}
            <div>
                <div class="form-label mb-1">明細</div>
                <table class="table">
                    <thead>
                    <tr>
                        <th>商品</th>
                        <th class="text-right">単価</th>
                        <th class="text-right">数量</th>
                        <th class="text-right">小計</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reservation->items as $it)
                        <tr>
                            <td>{{ $it->product->name }}</td>
                            <td class="text-right">¥{{ number_format($it->unit_price) }}</td>
                            <td class="text-right">{{ $it->quantity }}</td>
                            <td class="text-right">¥{{ number_format($it->subtotal) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- 備考（あれば表示） --}}
            @if($reservation->notes)
                <div>
                    <div class="form-label">備考</div>
                    <div class="whitespace-pre-line">{{ $reservation->notes }}</div>
                </div>
            @endif

            {{-- 戻るボタン --}}
            <div class="flex justify-end gap-2">
                <a href="{{ url()->previous() ?: route('reservations.index') }}" class="btn">
                    戻る
                </a>
            </div>
        </div>
    </div>
@endsection
