<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;

class RoleController extends Controller
{
    public function index()
    {
        $labels = UserRole::labels();
        $counts = User::query()
            ->selectRaw('role, count(*) as cnt')
            ->groupBy('role')->pluck('cnt','role');

        return view('roles.index', compact('labels','counts'));
    }
}
