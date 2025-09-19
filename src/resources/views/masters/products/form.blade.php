@extends('layouts.app')
@section('content')
    <h2 class="text-xl font-bold mb-4">{{ $row->exists ? '商品を編集':'商品を新規作成' }}</h2>
    <form method="post" action="{{ $row->exists ? route('products.update',$row) : route('products.store') }}" class="space-y-4">
        @csrf
        @if($row->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">SKU</label>
                <input name="sku" class="form-input" value="{{ old('sku',$row->sku) }}" required>
                @error('sku')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">商品名</label>
                <input name="name" class="form-input" value="{{ old('name',$row->name) }}" required>
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">価格（円）</label>
                <input type="number" name="price" class="form-input" value="{{ old('price',$row->price) }}" min="0" required>
                @error('price')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">メーカー</label>
                <input name="manufacturer" class="form-input" value="{{ old('manufacturer',$row->manufacturer) }}">
                @error('manufacturer')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="border rounded p-4 space-y-2">
            <div class="font-semibold">取扱店舗</div>
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="is_all_store" value="1" @checked(old('is_all_store', $row->is_all_store ?? true))>
                <span>全店舗で販売</span>
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="radio" name="is_all_store" value="0" @checked(!old('is_all_store', $row->is_all_store ?? true))>
                <span>店舗限定で販売</span>
            </label>
            <div id="jsStoreSelect" class="mt-2 {{ old('is_all_store', $row->is_all_store ?? true) ? 'hidden' : '' }}">
                <label class="form-label">対象店舗を選択</label>
                <select name="store_ids[]" class="form-input" multiple size="5">
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" @selected(in_array($store->id, old('store_ids', $selectedStoreIds ?? [])))>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
                <div class="text-xs text-gray-500 mt-1">複数選択できます（Ctrl/⌘キー）。</div>
                @error('store_ids')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div>
            <label class="form-label">説明</label>
            <textarea name="description" class="form-input" rows="3">{{ old('description',$row->description) }}</textarea>
            @error('description')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active',$row->is_active) ? 'checked':'' }}> 有効
        </label>

        <button class="btn-primary">保存</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const radios = document.querySelectorAll('input[name="is_all_store"]');
            const storeBox = document.getElementById('jsStoreSelect');
            radios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.value === '0' && radio.checked) {
                        storeBox?.classList.remove('hidden');
                    } else if (radio.value === '1' && radio.checked) {
                        storeBox?.classList.add('hidden');
                    }
                });
            });
        });
    </script>
@endsection
