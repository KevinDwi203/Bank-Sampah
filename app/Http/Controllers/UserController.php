<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id')->paginate(20);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'nama' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:Administrator,Petugas,Nasabah',
        ]);

        // password di-hash otomatis oleh cast 'hashed' di model User
        User::create([
            'username' => $request->username,
            'nama' => $request->nama,
            'password' => $request->password,
            'role' => $request->role,
            'aktif' => $request->boolean('aktif'),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'nama' => 'required|string|max:255',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:Administrator,Petugas,Nasabah',
        ]);

        $data = [
            'username' => $request->username,
            'nama' => $request->nama,
            'role' => $request->role,
            'aktif' => $request->boolean('aktif'),
        ];

        // Cegah admin menonaktifkan / menurunkan role akunnya sendiri
        if ($user->id === auth()->id()) {
            $data['role'] = $user->role;
            $data['aktif'] = true;
        }

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dihapus');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}
