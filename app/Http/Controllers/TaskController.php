<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Listar todas as tarefas do usuário autenticado
     */
    public function index()
    {
        return Task::where('user_id', Auth::id())->get();
    }

    /**
     * Criar nova tarefa (somente admin)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'user_id' => 'required|exists:users,id'
        ]);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }



    /**
     * Atualizar tarefa existente (somente se for do usuário)
     */
    public function update(Request $request, Task $task)
    {
        // Libera admins para editar qualquer tarefa
        if (Auth::user()->is_admin !== true && $task->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->update($request->all());

        return response()->json($task);
    }

    /**
     * Deletar tarefa (somente se for do usuário)
     */
    public function destroy(Task $task)
    {
        // Libera admins para excluir qualquer tarefa
        if (Auth::user()->is_admin !== true && $task->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }

}
