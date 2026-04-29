<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private string $file = 'storage/app/data/categories.json';

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
        $categories = $this->read();
        $category = array_values(array_filter($categories, fn($c) => $c['id'] === $id))[0] ?? null;

        if (!$category) return response()->json(['message' => 'Category not found'], 404);

        return response()->json($category);
    }

    public function store(Request $request)
    {
        $categories = $this->read();

        $category = [
            'id'          => count($categories) ? max(array_column($categories, 'id')) + 1 : 1,
            'name'        => $request->name,
            'description' => $request->description,
        ];

        $categories[] = $category;
        $this->write($categories);

        return response()->json($category, 201);
    }

    public function update(Request $request, int $id)
    {
        $categories = $this->read();
        $index = array_search($id, array_column($categories, 'id'));

        if ($index === false) return response()->json(['message' => 'Category not found'], 404);

        $categories[$index] = array_merge($categories[$index], $request->only(['name', 'description']));
        $this->write($categories);

        return response()->json($categories[$index]);
    }

    public function destroy(int $id)
    {
        $categories = $this->read();
        $filtered = array_values(array_filter($categories, fn($c) => $c['id'] !== $id));

        if (count($filtered) === count($categories)) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $this->write($filtered);

        return response()->json(['message' => 'Category deleted']);
    }
}