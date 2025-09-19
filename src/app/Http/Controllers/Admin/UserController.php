<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->select(['id','name','email','role','created_at'])->latest()->paginate(20);
        $roleLabels = UserRole::labels();
        return view('users.index', compact('users','roleLabels'));
    }

    public function create()
    {
        $roles = UserRole::options();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'role'     => ['required', Rule::in(array_keys(UserRole::labels()))],
        ]);
        User::create($data);
        return redirect()->route('users.index')->with('success','ユーザーを作成しました');
    }

    public function edit(User $user)
    {
        $roles = UserRole::options();
        return view('users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8'],
            'role'     => ['required', Rule::in(array_keys(UserRole::labels()))],
        ]);
        // 空パスワードは更新しない
        if (empty($data['password'])) unset($data['password']);

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
