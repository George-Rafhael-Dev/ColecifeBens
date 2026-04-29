<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    private function getProdutos()
    {
        if (!Storage::exists('produtos.json')) {
            Storage::put('produtos.json', '[]');
        }

        return json_decode(Storage::get('produtos.json'), true) ?? [];
    }

    private function saveProdutos($produtos)
    {
        Storage::put('produtos.json', json_encode($produtos, JSON_PRETTY_PRINT));
    }

    private function usuarioExiste($id)
    {
        if (!Storage::exists('usuarios.json')) return false;

        $usuarios = json_decode(Storage::get('usuarios.json'), true) ?? [];

        foreach ($usuarios as $u) {
            if ($u['id_usuario'] == $id) {
                return true;
            }
        }

        return false;
    }

    private function categoriaExiste($id)
    {
        if (!Storage::exists('categorias.json')) return false;

        $categorias = json_decode(Storage::get('categorias.json'), true) ?? [];

        foreach ($categorias as $c) {
            if ($c['id_categoria'] == $id) {
                return true;
            }
        }

        return false;
    }

    public function index()
    {
        return response()->json($this->getProdutos());
    }

    public function store(Request $request)
    {
        if (!$this->usuarioExiste($request->id_usuario)) {
            return response()->json(['erro' => 'Usuário não existe'], 400);
        }

        if (!$this->categoriaExiste($request->id_categoria)) {
            return response()->json(['erro' => 'Categoria não existe'], 400);
        }

        $produtos = $this->getProdutos();

        $id = count($produtos) ? max(array_column($produtos, 'id_produto')) + 1 : 1;

        $novo = [
            'id_produto' => $id,
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'preco' => $request->preco,
            'raridade' => $request->raridade,
            'estado_item' => $request->estado_item,
            'estoque' => $request->estoque,
            'id_usuario' => $request->id_usuario,
            'id_categoria' => $request->id_categoria
        ];

        $produtos[] = $novo;

        $this->saveProdutos($produtos);

        return response()->json($novo, 201);
    }

    public function show($id)
    {
        foreach ($this->getProdutos() as $produto) {
            if ($produto['id_produto'] == $id) {
                return response()->json($produto);
            }
        }

        return response()->json(['erro' => 'Produto não encontrado'], 404);
    }

    public function update(Request $request, $id)
    {
        $produtos = $this->getProdutos();

        foreach ($produtos as &$produto) {
            if ($produto['id_produto'] == $id) {

                if ($request->id_usuario && !$this->usuarioExiste($request->id_usuario)) {
                    return response()->json(['erro' => 'Usuário não existe'], 400);
                }

                if ($request->id_categoria && !$this->categoriaExiste($request->id_categoria)) {
                    return response()->json(['erro' => 'Categoria não existe'], 400);
                }

                $produto['nome'] = $request->nome ?? $produto['nome'];
                $produto['descricao'] = $request->descricao ?? $produto['descricao'];
                $produto['preco'] = $request->preco ?? $produto['preco'];
                $produto['raridade'] = $request->raridade ?? $produto['raridade'];
                $produto['estado_item'] = $request->estado_item ?? $produto['estado_item'];
                $produto['estoque'] = $request->estoque ?? $produto['estoque'];
                $produto['id_usuario'] = $request->id_usuario ?? $produto['id_usuario'];
                $produto['id_categoria'] = $request->id_categoria ?? $produto['id_categoria'];

                $this->saveProdutos($produtos);

                return response()->json($produto);
            }
        }

        return response()->json(['erro' => 'Produto não encontrado'], 404);
    }

    public function destroy($id)
    {
        $produtos = $this->getProdutos();

        foreach ($produtos as $key => $produto) {
            if ($produto['id_produto'] == $id) {
                unset($produtos[$key]);

                $this->saveProdutos(array_values($produtos));

                return response()->json(['message' => 'Produto deletado']);
            }
        }

        return response()->json(['erro' => 'Produto não encontrado'], 404);
    }
}