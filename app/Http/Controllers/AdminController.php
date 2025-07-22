<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function users()
    {
        $user = Auth::user();

        if (!$user || !$user->is_admin) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return User::select('id', 'name', 'email', 'is_admin', 'created_at')->get();
    }

    public function tasks()
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return Task::with('user:id,name')->get();
    }
}
