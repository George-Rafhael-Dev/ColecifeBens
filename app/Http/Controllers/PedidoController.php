<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PedidoController extends Controller
{
    private function getPedidos()
    {
        if (!Storage::exists('pedidos.json')) {
            Storage::put('pedidos.json', '[]');
        }

        return json_decode(Storage::get('pedidos.json'), true) ?? [];
    }

    private function savePedidos($pedidos)
    {
        Storage::put('pedidos.json', json_encode($pedidos, JSON_PRETTY_PRINT));
    }

    private function usuarioExiste($id)
    {
        if (!Storage::exists('usuarios.json')) return false;

        $usuarios = json_decode(Storage::get('usuarios.json'), true) ?? [];

        foreach ($usuarios as $u) {
            if ($u['id_usuario'] == $id) return true;
        }

        return false;
    }

    private function buscarProduto($id)
    {
        if (!Storage::exists('produtos.json')) return null;

        $produtos = json_decode(Storage::get('produtos.json'), true) ?? [];

        foreach ($produtos as $p) {
            if ($p['id_produto'] == $id) return $p;
        }

        return null;
    }

    private function calcularTotal($produtosRequest)
    {
        $total = 0;

        foreach ($produtosRequest as $item) {
            $produto = $this->buscarProduto($item['id_produto']);

            if (!$produto) {
                return null; 
            }

            $total += $produto['preco'];
        }

        return $total;
    }

    private function getProdutosDetalhados($ids)
    {
        $resultado = [];

        foreach ($ids as $id) {
            $produto = $this->buscarProduto($id);

            if ($produto) {
                $resultado[] = [
                    'id_produto' => $produto['id_produto'],
                    'nome' => $produto['nome'],
                    'preco' => $produto['preco']
                ];
            }
        }

        return $resultado;
    }

    public function index()
    {
        $pedidos = $this->getPedidos();

        foreach ($pedidos as &$pedido) {
            $ids = array_column($pedido['produtos'], 'id_produto');
            $pedido['produtos'] = $this->getProdutosDetalhados($ids);
        }

        return response()->json($pedidos);
    }

    public function store(Request $request)
    {
        if (!$this->usuarioExiste($request->id_usuario)) {
            return response()->json(['erro' => 'Usuário não existe'], 400);
        }

        $total = $this->calcularTotal($request->produtos);

        if ($total === null) {
            return response()->json(['erro' => 'Produto inválido'], 400);
        }

        $pedidos = $this->getPedidos();

        $id = count($pedidos) ? max(array_column($pedidos, 'id_pedido')) + 1 : 1;

        $novo = [
            'id_pedido' => $id,
            'id_usuario' => $request->id_usuario,
            'produtos' => $request->produtos,
            'data_pedido' => now()->toDateTimeString(),
            'status' => 'pendente',
            'status_pagamento' => 'aguardando',
            'metodo_pagamento' => $request->metodo_pagamento,
            'data_pagamento' => null,
            'valor_total' => $total 
        ];

        $pedidos[] = $novo;

        $this->savePedidos($pedidos);

        return response()->json($novo, 201);
    }

    public function show($id)
    {
        foreach ($this->getPedidos() as $pedido) {
            if ($pedido['id_pedido'] == $id) {

                $ids = array_column($pedido['produtos'], 'id_produto');
                $pedido['produtos'] = $this->getProdutosDetalhados($ids);

                return response()->json($pedido);
            }
        }

        return response()->json(['erro' => 'Pedido não encontrado'], 404);
    }

    public function update(Request $request, $id)
    {
        $pedidos = $this->getPedidos();

        foreach ($pedidos as &$pedido) {
            if ($pedido['id_pedido'] == $id) {

                $pedido['status'] = $request->status ?? $pedido['status'];
                $pedido['status_pagamento'] = $request->status_pagamento ?? $pedido['status_pagamento'];
                $pedido['metodo_pagamento'] = $request->metodo_pagamento ?? $pedido['metodo_pagamento'];
                $pedido['data_pagamento'] = $request->data_pagamento ?? $pedido['data_pagamento'];

                $this->savePedidos($pedidos);

                return response()->json($pedido);
            }
        }

        return response()->json(['erro' => 'Pedido não encontrado'], 404);
    }

    public function destroy($id)
    {
        $pedidos = $this->getPedidos();

        foreach ($pedidos as $key => $pedido) {
            if ($pedido['id_pedido'] == $id) {
                unset($pedidos[$key]);

                $this->savePedidos(array_values($pedidos));

                return response()->json(['message' => 'Pedido deletado']);
            }
        }

        return response()->json(['erro' => 'Pedido não encontrado'], 404);
    }
}