<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriaController extends Controller
{
    private function getCategorias()
    {
        if (!Storage::exists('categorias.json')) {
            Storage::put('categorias.json', '[]');
        }

        $json = Storage::get('categorias.json');
        return json_decode($json, true) ?? [];
    }

    private function saveCategorias($categorias)
    {
        Storage::put('categorias.json', json_encode($categorias, JSON_PRETTY_PRINT));
    }

    public function index()
    {
        return response()->json($this->getCategorias());
    }

    public function store(Request $request)
    {
        $categorias = $this->getCategorias();

        $id = count($categorias) ? max(array_column($categorias, 'id_categoria')) + 1 : 1;

        $nova = [
            'id_categoria' => $id,
            'nome' => $request->nome,
            'descricao' => $request->descricao
        ];

        $categorias[] = $nova;

        $this->saveCategorias($categorias);

        return response()->json($nova, 201);
    }

    public function show($id)
    {
        foreach ($this->getCategorias() as $categoria) {
            if ($categoria['id_categoria'] == $id) {
                return response()->json($categoria);
            }
        }

        return response()->json(['erro' => 'Categoria não encontrada'], 404);
    }

    public function update(Request $request, $id)
    {
        $categorias = $this->getCategorias();

        foreach ($categorias as &$categoria) {
            if ($categoria['id_categoria'] == $id) {

                $categoria['nome'] = $request->nome ?? $categoria['nome'];
                $categoria['descricao'] = $request->descricao ?? $categoria['descricao'];

                $this->saveCategorias($categorias);

                return response()->json($categoria);
            }
        }

        return response()->json(['erro' => 'Categoria não encontrada'], 404);
    }

    public function destroy($id)
    {
        $categorias = $this->getCategorias();

        foreach ($categorias as $key => $categoria) {
            if ($categoria['id_categoria'] == $id) {
                unset($categorias[$key]);

                $this->saveCategorias(array_values($categorias));

                return response()->json(['message' => 'Categoria deletada']);
            }
        }

        return response()->json(['erro' => 'Categoria não encontrada'], 404);
    }
}