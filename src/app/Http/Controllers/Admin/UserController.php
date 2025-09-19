<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->select(['id','name','email','role','store_id','created_at'])
            ->with('store:id,name')
            ->latest()
            ->paginate(20);
        $roleLabels = UserRole::labels();
        return view('users.index', compact('users','roleLabels'));
    }

    public function create()
    {
        $roles = UserRole::options();
        $stores = Store::orderBy('name')->get(['id','name']);
        return view('users.create', compact('roles','stores'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'role'     => ['required', Rule::in(array_keys(UserRole::labels()))],
            'store_id' => [
                'nullable',
                Rule::exists('stores','id'),
                Rule::requiredIf(fn() => $request->input('role') === UserRole::STORE),
            ],
        ]);

        if ($data['role'] !== UserRole::STORE) {
            $data['store_id'] = null;
        }
        User::create($data);
        return redirect()->route('users.index')->with('success','ユーザーを作成しました');
    }

    public function edit(User $user)
    {
        $roles = UserRole::options();
        $stores = Store::orderBy('name')->get(['id','name']);
        return view('users.edit', compact('user','roles','stores'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8'],
            'role'     => ['required', Rule::in(array_keys(UserRole::labels()))],
            'store_id' => [
                'nullable',
                Rule::exists('stores','id'),
                Rule::requiredIf(fn() => $request->input('role') === UserRole::STORE),
            ],
        ]);
        // 空パスワードは更新しない
        if (empty($data['password'])) unset($data['password']);

        if (($data['role'] ?? null) !== UserRole::STORE) {
            $data['store_id'] = null;
        }

        $user->update($data);
        return redirect()->route('users.index')->with('success','ユーザーを更新しました');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error','自分自身は削除できません');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success','ユーザーを削除しました');
    }
}
