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
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        if (!Auth::user()->is_admin) {
            return response()->json(['message' => 'Apenas administradores podem criar tarefas.'], 403);
        }

        $task = Task::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'is_completed' => false,
        ]);

        return response()->json($task, 201);
    }

    /**
     * Atualizar tarefa existente (somente se for do usuário)
     */
    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Você não tem permissão para editar esta tarefa.'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today',
            'is_completed' => 'boolean',
        ]);

        $task->update($request->only(['title', 'description', 'due_date', 'is_completed']));

        return response()->json($task);
    }

    /**
     * Deletar tarefa (somente se for do usuário)
     */
    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Você não tem permissão para deletar esta tarefa.'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Tarefa deletada com sucesso.'], 204);
    }
}
