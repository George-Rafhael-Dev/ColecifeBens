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
        Storage::put('pedidos.json', json_encode($pedidos));
    }

    private function getPrecoProduto($id)
    {
        if (!Storage::exists('produtos.json')) return 0;

        $produtos = json_decode(Storage::get('produtos.json'), true) ?? [];

        foreach ($produtos as $p) {
            if ($p['id_produto'] == $id) {
                return $p['preco'];
            }
        }

        return 0;
    }

    private function calcularTotal($produtos)
    {
        $total = 0;

        foreach ($produtos as $item) {
            $total += $this->getPrecoProduto($item['id_produto']);
        }

        return $total;
    }

    public function index()
    {
        return response()->json($this->getPedidos());
    }


    public function store(Request $request)
    {
        $pedidos = $this->getPedidos();

        $id = count($pedidos) ? max(array_column($pedidos, 'id_pedido')) + 1 : 1;

        $total = $this->calcularTotal($request->produtos);

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