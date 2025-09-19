@extends('layouts.app')

@section('title','予約一覧')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <form method="get" class="flex flex-wrap gap-2 items-end">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="氏名/電話" class="border p-2 rounded">
            <select name="store_id" class="border p-2 rounded">
                <option value="">店舗</option>
                @foreach($stores as $s)
                    <option value="{{ $s->id }}" @selected(request('store_id')==$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="campaign_id" class="border p-2 rounded">
                <option value="">企画</option>
                @foreach($campaigns as $c)
                    <option value="{{ $c->id }}" @selected(request('campaign_id')==$c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="border p-2 rounded">
            <input type="date" name="to" value="{{ request('to') }}" class="border p-2 rounded">
            <button class="px-4 py-2 bg-blue-600 text-white rounded">検索</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('reservations.create') }}" class="px-4 py-2 bg-green-600 text-white rounded">新規予約</a>
            <a href="{{ route('reservations.index') }}"
               class="px-4 py-2 bg-gray-300 text-gray-900 rounded">クリア</a>
            <a href="{{ route('reservations.export', request()->only(['store_id','campaign_id','from','to'])) }}"
               class="px-4 py-2 bg-slate-700 text-white rounded">CSV出力</a>
        </div>
    </div>

    <table class="table"> {{-- admin.css の .table を使う --}}
        <thead>
        <tr>
            <th>ID</th><th>登録日時</th><th>企画</th><th>店舗</th><th>氏名</th>
            <th>電話</th><th class="text-right">金額</th><th>状態</th><th>詳細</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reservations as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $r->campaign->name }}</td>
                <td>{{ $r->store->name }}</td>
                <td>{{ $r->customer_name }}</td>
                <td>{{ $r->phone }}</td>
                <td class="text-right">¥{{ number_format($r->total_amount) }}</td>
                <td>{{ $r->status }}</td>
                <td><a class="text-blue-600 underline" href="{{ route('reservations.show',$r) }}">見る</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $reservations->links() }}</div>
@endsection
