<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    private function getProdutos()
    {
        $json = Storage::get('produtos.json');
        return json_decode($json, true);
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

        $novo = [
            'id' => count($produtos) + 1,
            'nome' => $request->nome,
            'preco' => $request->preco
        ];

        $produtos[] = $novo;

        $this->saveProdutos($produtos);

        return response()->json($novo, 201);
    }

    public function show($id)
    {
        foreach ($this->getProdutos() as $produto) {
            if ($produto['id'] == $id) {
                return response()->json($produto);
            }
        }

        return response()->json(['erro' => 'Não encontrado'], 404);
    }

    public function update(Request $request, $id)
    {
        $produtos = $this->getProdutos();

        foreach ($produtos as &$produto) {
            if ($produto['id'] == $id) {
                $produto['nome'] = $request->nome ?? $produto['nome'];
                $produto['preco'] = $request->preco ?? $produto['preco'];

                $this->saveProdutos($produtos);

                return response()->json($produto);
            }
        }

        return response()->json(['erro' => 'Não encontrado'], 404);
    }

    public function destroy($id)
    {
        $produtos = $this->getProdutos();

        foreach ($produtos as $key => $produto) {
            if ($produto['id'] == $id) {
                unset($produtos[$key]);

                $this->saveProdutos(array_values($produtos));

                return response()->json(['message' => 'Deletado']);
            }
        }

        return response()->json(['erro' => 'Não encontrado'], 404);
    }
}