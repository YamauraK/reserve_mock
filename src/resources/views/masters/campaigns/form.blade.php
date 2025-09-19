@extends('layouts.app')

@section('content')
    <h2 class="text-xl font-bold mb-4">
        {{ $campaign->exists ? '企画を編集' : '企画を新規作成' }}
    </h2>

    {{-- エラー表示 --}}
    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">
            <div class="font-semibold mb-1">入力内容を確認してください</div>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post"
          action="{{ $campaign->exists ? route('campaigns.update', $campaign) : route('campaigns.store') }}"
          class="space-y-6">
        @csrf
        @if($campaign->exists) @method('PUT') @endif

        <div>
            <label class="form-label">企画名 <span class="text-red-600">*</span></label>
            <input name="name" class="form-input" required
                   value="{{ old('name', $campaign->name) }}">
            @error('name') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="form-label">説明</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description', $campaign->description) }}</textarea>
            @error('description') <div class="form-error">{{ $message }}</div> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">開始日</label>
                <input type="date" name="start_date" class="form-input"
                       value="{{ old('start_date', $campaign->start_date) }}">
                @error('start_date') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="form-label">終了日</label>
                <input type="date" name="end_date" class="form-input"
                       value="{{ old('end_date', $campaign->end_date) }}">
                @error('end_date') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $campaign->is_active) ? 'checked' : '' }}>
                    有効
                </label>
            </div>
        </div>

        <div>
            <div class="form-label">参加店舗 <span class="text-red-600">*</span></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                @foreach($stores as $store)
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="store_ids[]" value="{{ $store->id }}"
                               {{ in_array($store->id, old('store_ids', $selectedStoreIds ?? []), true) ? 'checked' : '' }}>
                        <span>{{ $store->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('store_ids') <div class="form-error">{{ $message }}</div> @enderror
            @error('store_ids.*') <div class="form-error">{{ $message }}</div> @enderror
            <div class="text-xs text-slate-500 mt-1">保存後に各店舗ごとの商品を設定できます。</div>
        </div>

        <div class="flex items-center gap-2">
            <button class="btn-primary">保存</button>
            <a href="{{ route('campaigns.index') }}" class="px-3 py-2 rounded border">戻る</a>

            @if($campaign->exists)
                <form method="post" action="{{ route('campaigns.destroy', $campaign) }}" class="inline ml-auto"
                      onsubmit="return confirm('削除しますか？')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-2 rounded bg-red-600 text-white">削除</button>
                </form>
            @endif
        </div>
    </form>

    @if($campaign->exists)
        <hr class="my-8">

        <div class="space-y-6">
            <h3 class="text-lg font-semibold">企画商品設定</h3>

            <form method="post" action="{{ route('campaigns.products.store', $campaign) }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                @csrf
                <div class="md:col-span-2">
                    <label class="form-label">店舗 <span class="text-red-600">*</span></label>
                    <select name="store_id" class="form-input" required>
                        <option value="">選択してください</option>
                        @foreach($campaign->stores as $store)
                            <option value="{{ $store->id }}" @selected(old('store_id')==$store->id)>{{ $store->name }}</option>
                        @endforeach
                    </select>
                    @error('store_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="form-label">商品 <span class="text-red-600">*</span></label>
                    <select name="product_id" class="form-input" required>
                        <option value="">選択してください</option>
                        @foreach($productOptions as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id')==$product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">計画数量</label>
                    <input type="number" name="planned_quantity" min="0" class="form-input" value="{{ old('planned_quantity', 0) }}">
                    @error('planned_quantity') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_available" value="0">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="is_available" value="1" {{ old('is_available', 1) ? 'checked' : '' }}>
                        有効
                    </label>
                    <button class="btn-primary">追加</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead>
                        <tr class="bg-slate-100 text-left">
                            <th class="px-3 py-2 border">店舗</th>
                            <th class="px-3 py-2 border">商品</th>
                            <th class="px-3 py-2 border">計画数量</th>
                            <th class="px-3 py-2 border">状態</th>
                            <th class="px-3 py-2 border w-48">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productStores as $row)
                            <tr>
                                <td class="px-3 py-2 border">{{ $row->store->name }}</td>
                                <td class="px-3 py-2 border">{{ $row->product->name }}</td>
                                <td class="px-3 py-2 border">
                                    <form method="post" action="{{ route('campaigns.products.update', [$campaign, $row]) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="planned_quantity" min="0" class="form-input w-24"
                                               value="{{ $row->planned_quantity }}">
                                        <select name="is_available" class="form-input w-32">
                                            <option value="1" {{ $row->is_available ? 'selected' : '' }}>有効</option>
                                            <option value="0" {{ !$row->is_available ? 'selected' : '' }}>停止</option>
                                        </select>
                                        <button class="px-3 py-1 bg-blue-600 text-white rounded">更新</button>
                                    </form>
                                </td>
                                <td class="px-3 py-2 border">
                                    <span class="px-2 py-1 rounded text-xs {{ $row->is_available ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $row->is_available ? '販売中' : '停止中' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 border">
                                    <form method="post" action="{{ route('campaigns.products.destroy', [$campaign, $row]) }}" onsubmit="return confirm('削除しますか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-1 rounded bg-red-600 text-white">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-slate-500">設定済みの商品がありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
