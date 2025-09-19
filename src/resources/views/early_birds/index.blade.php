@extends('layouts.app')
@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold">早期予約割引 一覧</h1>
            <a href="{{ route('early-birds.create') }}" class="btn-primary">新規作成</a>
        </div>

        @if(session('success')) <div class="mb-3 text-green-700">{{ session('success') }}</div> @endif

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>企画</th>
                    <th>名称</th>
                    <th>期間</th>
                    <th>割引</th>
                    <th>チャネル</th>
                    <th>状態</th>
                    <th class="text-right"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->campaign->name ?? '-' }}</td>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->starts_at?->toDateString() ?? '—' }} 〜 {{ $r->cutoff_date?->toDateString() }}</td>
                        <td>{{ $r->display_value }}</td>
                        <td>{{ $r->channelsLabel() ?: '全チャネル' }}</td>
                        <td>
                            <span class="badge {{ $r->is_active ? 'ok':'ng' }}">{{ $r->is_active ? '有効':'無効' }}</span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('early-birds.edit',$r) }}" class="text-blue-600 hover:underline">編集</a>
                            <form action="{{ route('early-birds.destroy',$r) }}" method="post" class="inline ml-2"
                                  onsubmit="return confirm('削除しますか？');">
                                @csrf @method('delete')
                                <button class="text-red-600 hover:underline">削除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    </div>
@endsection
