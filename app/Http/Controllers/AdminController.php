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
}
