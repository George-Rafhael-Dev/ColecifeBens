<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    private string $file = 'storage/app/data/users.json';

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
        $users = $this->read();
        $user = array_values(array_filter($users, fn($u) => $u['id'] === $id))[0] ?? null;

        if (!$user) return response()->json(['message' => 'User not found'], 404);

        return response()->json($user);
    }

    public function store(Request $request)
    {
        $users = $this->read();

        $user = [
            'id'             => count($users) ? max(array_column($users, 'id')) + 1 : 1,
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => bcrypt($request->password),
            'cpf'            => $request->cpf,
            'birth_date'     => $request->birth_date,
            'phone'          => $request->phone,
            'reputation'     => 0,
            'registered_at'  => now()->toDateTimeString(),
        ];

        $users[] = $user;
        $this->write($users);

        return response()->json($user, 201);
    }

    public function update(Request $request, int $id)
    {
        $users = $this->read();
        $index = array_search($id, array_column($users, 'id'));

        if ($index === false) return response()->json(['message' => 'User not found'], 404);

        $users[$index] = array_merge($users[$index], $request->only([
            'name', 'email', 'phone', 'birth_date'
        ]));

        $this->write($users);

        return response()->json($users[$index]);
    }

    public function destroy(int $id)
    {
        $users = $this->read();
        $filtered = array_values(array_filter($users, fn($u) => $u['id'] !== $id));

        if (count($filtered) === count($users)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->write($filtered);

        return response()->json(['message' => 'User deleted']);
    }
}