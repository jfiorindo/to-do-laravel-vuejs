<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{


    public function alterarSenha(Request $request)
    {
        $request->validate([
            'senhaAtual' => 'required',
            'novaSenha' => 'required|min:6',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->senhaAtual, $user->password)) {
            return response()->json(['erro' => 'Senha atual incorreta.'], 422);
        }

        $user->password = Hash::make($request->novaSenha);
        $user->save();

        return response()->json(['mensagem' => 'Senha alterada com sucesso.']);
    }

}
