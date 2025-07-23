<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public function exportTasks()
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $tarefas = Task::with('user')->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=tarefas_admin.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($tarefas) {
            $handle = fopen('php://output', 'w');

            // Cabeçalhos
            fputcsv($handle, ['ID', 'Título', 'Descrição', 'Data de Vencimento', 'Concluída', 'Usuário']);

            foreach ($tarefas as $tarefa) {
                fputcsv($handle, [
                    $tarefa->id,
                    $tarefa->title,
                    $tarefa->description,
                    $tarefa->due_date,
                    $tarefa->is_completed ? 'Sim' : 'Não',
                    $tarefa->user->name ?? 'Desconhecido'
                ]);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function showUser($id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $user = User::withCount([
            'tasks as feitas' => fn($q) => $q->where('is_completed', true),
            'tasks as pendentes' => fn($q) => $q->where('is_completed', false),
        ])->findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => $user->is_admin,
            'created_at' => $user->created_at,
            'feitas' => $user->feitas,
            'pendentes' => $user->pendentes,
        ]);
    }
    public function storeUser(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'is_admin' => 'required|boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        return User::create($validated);
    }
    public function updateUser(Request $request, $id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'is_admin' => 'sometimes|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return $user;
    }
    public function deleteUser($id)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return response()->json([
            'success' => User::destroy($id) > 0
        ]);
    }




}
