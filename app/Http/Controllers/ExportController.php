<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function export()
    {
        $user = Auth::user();

        $tasks = $user->is_admin
            ? Task::with('user')->get()
            : Task::where('user_id', $user->id)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tarefas.csv"',
        ];

        $callback = function () use ($tasks) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Título', 'Descrição', 'Data de Vencimento', 'Concluída', 'Usuário']);

            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->id,
                    $task->title,
                    $task->description,
                    $task->due_date,
                    $task->is_completed ? 'Sim' : 'Não',
                    $task->user->name ?? 'N/A'
                ]);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
