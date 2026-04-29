<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReviewController extends Controller
{
    private string $file = 'storage/app/data/reviews.json';

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
        $reviews = $this->read();
        $review = array_values(array_filter($reviews, fn($r) => $r['id'] === $id))[0] ?? null;

        if (!$review) return response()->json(['message' => 'Review not found'], 404);

        return response()->json($review);
    }

    public function store(Request $request)
    {
        $reviews = $this->read();

        $review = [
            'id'           => count($reviews) ? max(array_column($reviews, 'id')) + 1 : 1,
            'user_id'      => $request->user_id,
            'product_id'   => $request->product_id,
            'rating'       => $request->rating,
            'comment'      => $request->comment,
            'reviewed_at'  => now()->toDateTimeString(),
        ];

        $reviews[] = $review;
        $this->write($reviews);

        return response()->json($review, 201);
    }

    public function destroy(int $id)
    {
        $reviews = $this->read();
        $filtered = array_values(array_filter($reviews, fn($r) => $r['id'] !== $id));

        if (count($filtered) === count($reviews)) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        $this->write($filtered);

        return response()->json(['message' => 'Review deleted']);
    }
}