<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    private string $file = 'storage/app/data/products.json';

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
        $products = $this->read();
        $product = array_values(array_filter($products, fn($p) => $p['id'] === $id))[0] ?? null;

        if (!$product) return response()->json(['message' => 'Product not found'], 404);

        return response()->json($product);
    }

    public function store(Request $request)
    {
        $products = $this->read();

        $product = [
            'id'          => count($products) ? max(array_column($products, 'id')) + 1 : 1,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'rarity'      => $request->rarity,
            'condition'   => $request->condition,
            'stock'       => $request->stock,
            'user_id'     => $request->user_id,
            'category_id' => $request->category_id,
        ];

        $products[] = $product;
        $this->write($products);

        return response()->json($product, 201);
    }

    public function update(Request $request, int $id)
    {
        $products = $this->read();
        $index = array_search($id, array_column($products, 'id'));

        if ($index === false) return response()->json(['message' => 'Product not found'], 404);

        $products[$index] = array_merge($products[$index], $request->only([
            'name', 'description', 'price', 'rarity', 'condition', 'stock', 'category_id'
        ]));

        $this->write($products);

        return response()->json($products[$index]);
    }

    public function destroy(int $id)
    {
        $products = $this->read();
        $filtered = array_values(array_filter($products, fn($p) => $p['id'] !== $id));

        if (count($filtered) === count($products)) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $this->write($filtered);

        return response()->json(['message' => 'Product deleted']);
    }
}