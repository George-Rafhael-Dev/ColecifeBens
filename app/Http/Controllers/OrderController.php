<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    private string $file = 'storage/app/data/orders.json';

    private function read(): array
    {
        return json_decode(file_get_contents(base_path($this->file)), true) ?? [];
    }

    private function write(array $data): void
    {
        file_put_contents(base_path($this->file), json_encode($data, JSON_PRETTY_PRINT));
    }

    public function index()
    {
        return response()->json($this->read());
    }

    public function show(int $id)
    {
        $orders = $this->read();
        $order = array_values(array_filter($orders, fn($o) => $o['id'] === $id))[0] ?? null;

        if (!$order) return response()->json(['message' => 'Order not found'], 404);

        return response()->json($order);
    }

    public function store(Request $request)
    {
        $orders = $this->read();

        $order = [
            'id'             => count($orders) ? max(array_column($orders, 'id')) + 1 : 1,
            'user_id'        => $request->user_id,
            'product_ids'    => $request->product_ids,
            'total'          => $request->total,
            'status'         => 'pending',
            'payment_status' => 'awaiting',
            'payment_method' => $request->payment_method,
            'ordered_at'     => now()->toDateTimeString(),
        ];

        $orders[] = $order;
        $this->write($orders);

        return response()->json($order, 201);
    }

    public function update(Request $request, int $id)
    {
        $orders = $this->read();
        $index = array_search($id, array_column($orders, 'id'));

        if ($index === false) return response()->json(['message' => 'Order not found'], 404);

        $orders[$index] = array_merge($orders[$index], $request->only([
            'status', 'payment_status', 'payment_method'
        ]));

        $this->write($orders);

        return response()->json($orders[$index]);
    }

    public function destroy(int $id)
    {
        $orders = $this->read();
        $filtered = array_values(array_filter($orders, fn($o) => $o['id'] !== $id));

        if (count($filtered) === count($orders)) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $this->write($filtered);

        return response()->json(['message' => 'Order deleted']);
    }
}