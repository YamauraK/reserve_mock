<table class="table">
    <thead><tr>
        <th>ID</th><th>企画名</th><th>期間</th><th>参加店舗</th><th>状態</th><th></th>
    </tr></thead>
    <tbody>
    @foreach($rows as $r)
        <tr>
            <td>{{ $r->id }}</td>
            <td>{{ $r->name }}</td>
            <td>{{ $r->start_date }} 〜 {{ $r->end_date }}</td>
            <td>
                @php($storeNames = $r->productStores->pluck('store.name')->filter()->unique()->values()->all())
                @if(empty($storeNames))
                    <span class="text-gray-500">未設定</span>
                @else
                    {{ implode('、', $storeNames) }}
                @endif
            </td>
            <td><span class="badge {{ $r->is_active ? 'ok':'ng' }}">{{ $r->is_active ? '有効':'無効' }}</span></td>
            <td class="text-right">
                <a href="{{ route('campaigns.edit',$r) }}" class="text-blue-600 underline">編集</a>
                <form method="post" action="{{ route('campaigns.destroy',$r) }}" class="inline">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('削除しますか？')" class="text-red-600 underline ml-2">削除</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="mt-3">{{ $rows->links() }}</div>
