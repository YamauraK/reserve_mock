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

    @php
        $defaultSelectedStoreIds = $selectedStoreIds ?? [];
        $defaultStoreProductSelections = $storeProductSelections ?? [];
        $storeProductOptions = $storeProductOptions ?? [];
        $selectedStoreIds = collect(old('store_ids', $defaultSelectedStoreIds))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $storeProductSelections = old('store_products') !== null
            ? collect(old('store_products'))
                ->mapWithKeys(function ($productIds, $storeId) {
                    return [
                        (int) $storeId => collect($productIds)
                            ->map(fn($id) => (int) $id)
                            ->filter()
                            ->unique()
                            ->values()
                            ->all(),
                    ];
                })
                ->all()
            : $defaultStoreProductSelections;
    @endphp

    <form method="post"
          action="{{ $campaign->exists ? route('campaigns.update', $campaign) : route('campaigns.store') }}"
          class="space-y-4">
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

        <div class="border rounded p-4 space-y-3">
            <div class="font-semibold">参加店舗と企画対象商品</div>
            <div class="text-sm text-gray-600">参加する店舗を選択し、企画で取り扱う商品を店舗ごとに設定します。</div>

            <div class="space-y-3">
                @foreach($stores as $store)
                    @php
                        $isChecked = in_array($store->id, $selectedStoreIds, true);
                        $selectedProducts = $storeProductSelections[$store->id] ?? [];
                    @endphp
                    <div class="border rounded p-3 space-y-2">
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox"
                                   name="store_ids[]"
                                   value="{{ $store->id }}"
                                   class="js-store-toggle"
                                   data-target="store-products-{{ $store->id }}"
                                   @checked($isChecked)>
                            <span>{{ $store->name }}</span>
                        </label>
                        <div id="store-products-{{ $store->id }}" class="space-y-2 {{ $isChecked ? '' : 'hidden' }}">
                            <label class="form-label">企画対象商品</label>
                            <select name="store_products[{{ $store->id }}][]"
                                    class="form-input"
                                    multiple
                                    size="5"
                                    {{ $isChecked ? '' : 'disabled' }}>
                                @foreach($storeProductOptions[$store->id] ?? [] as $product)
                                    <option value="{{ $product['id'] }}" @selected(in_array($product['id'], $selectedProducts, true))>
                                        {{ $product['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="text-xs text-gray-500">複数選択できます（Ctrl/⌘キー）。</div>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('store_ids') <div class="form-error">{{ $message }}</div> @enderror
            @error('store_products') <div class="form-error">{{ $message }}</div> @enderror
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggles = document.querySelectorAll('.js-store-toggle');
            toggles.forEach((checkbox) => {
                const targetId = checkbox.dataset.target;
                const target = targetId ? document.getElementById(targetId) : null;
                const select = target ? target.querySelector('select') : null;
                if (!checkbox.checked && select) {
                    select.setAttribute('disabled', 'disabled');
                }
                checkbox.addEventListener('change', () => {
                    const el = targetId ? document.getElementById(targetId) : null;
                    const selectEl = el ? el.querySelector('select') : null;
                    if (!el || !selectEl) {
                        return;
                    }
                    if (checkbox.checked) {
                        el.classList.remove('hidden');
                        selectEl.removeAttribute('disabled');
                    } else {
                        el.classList.add('hidden');
                        selectEl.setAttribute('disabled', 'disabled');
                        selectEl.querySelectorAll('option').forEach(option => {
                            option.selected = false;
                        });
                    }
                });
            });
        });
    </script>
@endsection
