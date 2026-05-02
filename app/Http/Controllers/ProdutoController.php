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
        Storage::put('produtos.json', json_encode($produtos));
    }

    public function index()
    {
        return response()->json($this->getProdutos());
    }

    public function store(Request $request)
    {
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