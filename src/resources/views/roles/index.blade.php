@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto p-6">
        <h1 class="text-xl font-bold mb-4">権限管理</h1>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="table">
                <thead>
                <tr>
                    <th>権限</th>
                    <th>説明</th>
                    <th class="text-right">ユーザー数</th>
                </tr>
                </thead>
                <tbody>
                @foreach($labels as $value => $label)
                    <tr>
                        <td>
                <span class="badge {{ $value===\App\Enums\UserRole::HQ ? 'role-hq' : 'role-store' }}">
                  {{ $label }}
                </span>
                        </td>
                        <td>
                            @if($value===\App\Enums\UserRole::HQ)
                                本店：全店舗の閲覧・管理が可能
                            @else
                                店舗：自店舗中心の閲覧（将来の権限制御で制約）
                            @endif
                        </td>
                        <td class="text-right">{{ $counts[$value] ?? 0 }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
