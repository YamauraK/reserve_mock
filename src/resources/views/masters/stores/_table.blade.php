<table class="table">
    <thead>
    <tr>
        <th>ID</th><th>店舗コード</th><th>店舗名</th><th>電話</th><th>営業時間</th><th>状態</th><th></th>
    </tr>
    </thead>
    <tbody>
    @forelse($rows as $r)
        <tr>
            <td>{{ $r->id }}</td>
            <td>{{ $r->code }}</td>
            <td>{{ $r->name }}</td>
            <td>{{ $r->phone }}</td>
            <td>
                {{ $r->open_time ? \Illuminate\Support\Str::of($r->open_time)->limit(5,'') : '-' }}
                〜
                {{ $r->close_time ? \Illuminate\Support\Str::of($r->close_time)->limit(5,'') : '-' }}
            </td>
            <td><span class="badge {{ $r->is_active ? 'ok':'ng' }}">{{ $r->is_active ? '有効':'無効' }}</span></td>
            <td class="text-right">
                <a href="{{ route('stores.edit',$r) }}" class="text-blue-600 underline">編集</a>
                <form method="post" action="{{ route('stores.destroy',$r) }}" class="inline">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('削除しますか？')" class="text-red-600 underline ml-2">削除</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="p-4 text-center text-gray-500">データがありません</td></tr>
    @endforelse
    </tbody>
</table>
<div class="mt-3">{{ $rows->links() }}</div>
