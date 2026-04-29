<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    private function getUsuarios()
    {
        if (!Storage::exists('usuarios.json')) {
            Storage::put('usuarios.json', '[]');
        }

        $json = Storage::get('usuarios.json');
        return json_decode($json, true) ?? [];
    }

    private function saveUsuarios($usuarios)
    {
        Storage::put('usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));
    }

    public function index()
    {
        return response()->json($this->getUsuarios());
    }

    public function store(Request $request)
    {
        $usuarios = $this->getUsuarios();

        $id = count($usuarios) ? max(array_column($usuarios, 'id_usuario')) + 1 : 1;

        $novo = [
            'id_usuario' => $id,
            'nome' => $request->nome,
            'email' => $request->email,
            'senha' => $request->senha,
            'cpf' => $request->cpf,
            'data_nascimento' => $request->data_nascimento,
            'telefone' => $request->telefone,
            'reputacao' => $request->reputacao ?? 0,
            'data_cadastro' => now()->toDateTimeString()
        ];

        $usuarios[] = $novo;

        $this->saveUsuarios($usuarios);

        return response()->json($novo, 201);
    }

    public function show($id)
    {
        foreach ($this->getUsuarios() as $usuario) {
            if ($usuario['id_usuario'] == $id) {
                return response()->json($usuario);
            }
        }

        return response()->json(['erro' => 'Usuário não encontrado'], 404);
    }

    public function update(Request $request, $id)
    {
        $usuarios = $this->getUsuarios();

        foreach ($usuarios as &$usuario) {
            if ($usuario['id_usuario'] == $id) {

                $usuario['nome'] = $request->nome ?? $usuario['nome'];
                $usuario['email'] = $request->email ?? $usuario['email'];
                $usuario['senha'] = $request->senha ?? $usuario['senha'];
                $usuario['cpf'] = $request->cpf ?? $usuario['cpf'];
                $usuario['data_nascimento'] = $request->data_nascimento ?? $usuario['data_nascimento'];
                $usuario['telefone'] = $request->telefone ?? $usuario['telefone'];
                $usuario['reputacao'] = $request->reputacao ?? $usuario['reputacao'];

                $this->saveUsuarios($usuarios);

                return response()->json($usuario);
            }
        }

        return response()->json(['erro' => 'Usuário não encontrado'], 404);
    }

    public function destroy($id)
    {
        $usuarios = $this->getUsuarios();

        foreach ($usuarios as $key => $usuario) {
            if ($usuario['id_usuario'] == $id) {
                unset($usuarios[$key]);

                $this->saveUsuarios(array_values($usuarios));

                return response()->json(['message' => 'Usuário deletado']);
            }
        }

        return response()->json(['erro' => 'Usuário não encontrado'], 404);
    }
}