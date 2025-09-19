<table class="table">
    <thead>
    <tr>
        <th>ID</th><th>SKU</th><th>商品名</th><th>価格</th><th>メーカー</th><th>状態</th><th></th>
    </tr>
    </thead>
    <tbody>
    @forelse($rows as $r)
        <tr>
            <td>{{ $r->id }}</td>
            <td>{{ $r->sku }}</td>
            <td>{{ $r->name }}</td>
            <td>¥{{ number_format($r->price) }}</td>
            <td>{{ $r->manufacturer }}</td>
            <td><span class="badge {{ $r->is_active ? 'ok':'ng' }}">{{ $r->is_active ? '有効':'無効' }}</span></td>
            <td class="text-right">
                <a href="{{ route('products.edit',$r) }}" class="text-blue-600 underline">編集</a>
                <form method="post" action="{{ route('products.destroy',$r) }}" class="inline">
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
