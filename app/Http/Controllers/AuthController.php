<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect(auth()->user()->homeRoute());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');
        $credentials['aktif'] = true; // akun nonaktif tidak boleh login

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended($request->user()->homeRoute());
        }

        throw ValidationException::withMessages([
            'username' => 'Username atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function profile(Request $request)
    {
        return view('auth.profile', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:100|unique:users,email,' . $user->id,
            'telepon' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\\-\\s()]+$/'],
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'telepon.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, +, - dan tanda kurung.',
        ]);

        $user->nama = $request->nama;
        $user->email = $request->filled('email') ? $request->email : null;
        $user->telepon = $request->filled('telepon') ? $request->telepon : null;
        if ($request->filled('password')) {
            $user->password = $request->password; // di-hash otomatis oleh cast model
        }
        $user->save();

        // Untuk akun Nasabah, nomor telepon juga disamakan dengan data nasabah
        // agar terlihat di menu Data Nasabah (hanya data milik akun ini).
        if ($user->isNasabah() && $user->nasabah && $request->filled('telepon')) {
            $user->nasabah->update(['telepon' => $request->telepon]);
        }

        return back()->with('success', 'Profil berhasil diperbarui');
    }
}