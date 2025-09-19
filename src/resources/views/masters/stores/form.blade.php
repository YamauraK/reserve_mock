@extends('layouts.app')
@section('content')
    <h2 class="text-xl font-bold mb-4">{{ $row->exists ? '店舗を編集':'店舗を新規作成' }}</h2>
    <form method="post" action="{{ $row->exists ? route('stores.update',$row) : route('stores.store') }}" class="space-y-4">
        @csrf
        @if($row->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">店舗コード</label>
                <input name="code" class="form-input" value="{{ old('code',$row->code) }}" required>
                @error('code')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">店舗名</label>
                <input name="name" class="form-input" value="{{ old('name',$row->name) }}" required>
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">電話</label>
                <input name="phone" class="form-input" value="{{ old('phone',$row->phone) }}">
                @error('phone')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">住所</label>
                <input name="address" class="form-input" value="{{ old('address',$row->address) }}">
                @error('address')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">開店時間</label>
                <input type="time" name="open_time" class="form-input" value="{{ old('open_time',$row->open_time) }}">
                @error('open_time')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label class="form-label">閉店時間</label>
                <input type="time" name="close_time" class="form-input" value="{{ old('close_time',$row->close_time) }}">
                @error('close_time')<div class="form-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active',$row->is_active) ? 'checked':'' }}> 有効
        </label>

        <button class="btn-primary">保存</button>
    </form>
@endsection
