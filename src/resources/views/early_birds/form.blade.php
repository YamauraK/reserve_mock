@csrf
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="form-label">企画 <span class="text-red-600">*</span></label>
        <select name="campaign_id" class="form-input" required>
            <option value="">選択してください</option>
            @foreach($campaigns as $c)
                <option value="{{ $c->id }}" @selected(old('campaign_id',$model->campaign_id)==$c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('campaign_id')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="form-label">名称 <span class="text-red-600">*</span></label>
        <input name="name" class="form-input" value="{{ old('name',$model->name) }}" required>
        @error('name')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="form-label">開始日</label>
        <input type="date" name="starts_at" class="form-input" value="{{ old('starts_at', optional($model->starts_at)->toDateString()) }}">
        @error('starts_at')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="form-label">締切日 <span class="text-red-600">*</span></label>
        <input type="date" name="cutoff_date" class="form-input" value="{{ old('cutoff_date', optional($model->cutoff_date)->toDateString()) }}" required>
        @error('cutoff_date')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="form-label">割引種別 / 値 <span class="text-red-600">*</span></label>
        <div class="flex gap-2">
            <select name="discount_type" class="form-input" required>
                @foreach(['percent'=>'割合(%)','amount'=>'金額(円)'] as $v=>$lab)
                    <option value="{{ $v }}" @selected(old('discount_type',$model->discount_type)===$v)>{{ $lab }}</option>
                @endforeach
            </select>
            <input name="discount_value" type="number" min="1" class="form-input"
                   value="{{ old('discount_value',$model->discount_value) }}" required>
        </div>
        @error('discount_type')<div class="form-error">{{ $message }}</div>@enderror
        @error('discount_value')<div class="form-error">{{ $message }}</div>@enderror
    </div>

    <div>
        <label class="form-label">チャネル</label>
        @php $chs = old('channels', $model->channels ?? []); @endphp
        <div class="grid grid-cols-3 gap-2">
            @foreach([ 'store'=>'店頭', 'tokushimaru'=>'とくし丸', 'web'=>'Web'] as $val=>$lab)
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="channels[]" value="{{ $val }}"
                        {{ in_array($val,(array)$chs,true) ? 'checked':'' }}>
                    {{ $lab }}
                </label>
            @endforeach
        </div>
        <div class="text-xs text-slate-500 mt-1">未選択の場合は全チャネル扱い</div>
    </div>

    <div>
        <label class="form-label">併用可否</label>
        <select name="stackable" class="form-input">
            <option value="0" @selected(old('stackable',$model->stackable)==0)>併用しない（おすすめ）</option>
            <option value="1" @selected(old('stackable',$model->stackable)==1)>他割引と併用可</option>
        </select>
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active',$model->is_active) ? 'checked':'' }}>
            有効
        </label>
    </div>
</div>

<hr class="my-6">

{{-- スコープ（商品/店舗を絞る） --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="form-label">対象商品</label>
        <select name="product_ids[]" class="form-input" multiple>
            @foreach($products as $p)
                <option value="{{ $p->id }}"
                    @selected(in_array($p->id, old('product_ids', $selectedProductIds ?? [])))>
                    {{ $p->name }}
                </option>
            @endforeach
        </select>
        <div class="text-xs text-slate-500 mt-1">未選択＝全商品</div>
    </div>

    <div>
        <label class="form-label">対象店舗</label>
        <select name="store_ids[]" class="form-input" multiple>
            @foreach($stores as $s)
                <option value="{{ $s->id }}"
                    @selected(in_array($s->id, old('store_ids', $selectedStoreIds ?? [])))>
                    {{ $s->name }}
                </option>
            @endforeach
        </select>
        <div class="text-xs text-slate-500 mt-1">未選択＝全店舗</div>
    </div>
</div>

<div class="flex gap-2 mt-6">
    <button class="btn-primary">{{ $model->exists ? '更新':'作成' }}</button>
    <a href="{{ route('early-birds.index') }}" class="btn-outline">戻る</a>
</div>
