<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvaliacaoController extends Controller
{
    private function getAvaliacoes()
    {
        if (!Storage::exists('avaliacoes.json')) {
            Storage::put('avaliacoes.json', '[]');
        }

        return json_decode(Storage::get('avaliacoes.json'), true) ?? [];
    }

    private function saveAvaliacoes($avaliacoes)
    {
        Storage::put('avaliacoes.json', json_encode($avaliacoes));
    }

    public function index()
    {
        return response()->json($this->getAvaliacoes());
    }

    public function store(Request $request)
    {
        if ($request->nota < 0 || $request->nota > 5) {
            return response()->json(['erro' => 'Nota deve ser entre 0 e 5'], 400);
        }

        $avaliacoes = $this->getAvaliacoes();

        $id = count($avaliacoes) ? max(array_column($avaliacoes, 'id_avaliacao')) + 1 : 1;

        $nova = [
            'id_avaliacao' => $id,
            'id_usuario' => $request->id_usuario,
            'id_produto' => $request->id_produto,
            'nota' => $request->nota,
            'comentario' => $request->comentario,
            'data_avaliacao' => now()->toDateTimeString()
        ];

        $avaliacoes[] = $nova;

        $this->saveAvaliacoes($avaliacoes);

        return response()->json($nova, 201);
    }

    public function show($id)
    {
        foreach ($this->getAvaliacoes() as $avaliacao) {
            if ($avaliacao['id_avaliacao'] == $id) {
                return response()->json($avaliacao);
            }
        }

        return response()->json(['erro' => 'Avaliação não encontrada'], 404);
    }


    public function update(Request $request, $id)
    {
        $avaliacoes = $this->getAvaliacoes();

        foreach ($avaliacoes as &$avaliacao) {
            if ($avaliacao['id_avaliacao'] == $id) {

                if ($request->nota !== null) {
                    if ($request->nota < 0 || $request->nota > 5) {
                        return response()->json(['erro' => 'Nota inválida'], 400);
                    }
                    $avaliacao['nota'] = $request->nota;
                }

                $avaliacao['comentario'] = $request->comentario ?? $avaliacao['comentario'];

                $this->saveAvaliacoes($avaliacoes);

                return response()->json($avaliacao);
            }
        }

        return response()->json(['erro' => 'Avaliação não encontrada'], 404);
    }

    public function destroy($id)
    {
        $avaliacoes = $this->getAvaliacoes();

        foreach ($avaliacoes as $key => $avaliacao) {
            if ($avaliacao['id_avaliacao'] == $id) {
                unset($avaliacoes[$key]);

                $this->saveAvaliacoes(array_values($avaliacoes));

                return response()->json(['message' => 'Avaliação deletada']);
            }
        }

        return response()->json(['erro' => 'Avaliação não encontrada'], 404);
    }
}