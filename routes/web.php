<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirecionar '/' com base na autenticação
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->is_admin
        ? redirect()->route('admin.dashboard')
        : redirect()->route('tasks');
})->name('home');

// Redirecionamento inteligente da rota /dashboard (caso venha do Breeze)
Route::get('/dashboard', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->is_admin
        ? redirect()->route('admin.dashboard')
        : redirect()->route('tasks');
})->name('dashboard');

// Rotas protegidas por autenticação
Route::middleware(['auth', 'verified'])->group(function () {

    // Painel de tarefas do usuário comum
    Route::get('/tasks', function () {
        return Inertia::render('TasksView');
    })->name('tasks');

    // Painel do administrador
    Route::get('/admin', function () {
        if (!auth()->user()->is_admin) {
            abort(403);
        }
        return Inertia::render('AdminDashboard');
    })->name('admin.dashboard');

    // Tela de gerenciamento de usuários (apenas para admin)
    Route::get('/admin/users', function () {
        if (!auth()->user()->is_admin) {
            abort(403);
        }
        return Inertia::render('AdminUsers');
    })->name('admin.users');

    // Perfil do usuário autenticado
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Rotas da API (definidas em routes/api.php)
Route::middleware('api')
    ->prefix('api')
    ->group(base_path('routes/api.php'));

// Autenticação (login, registro, etc.)
require __DIR__.'/auth.php';
