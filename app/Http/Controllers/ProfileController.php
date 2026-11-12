<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile', ['user' => auth()->user()]);
    }

    /** Perbarui username akun yang sedang login. */
    public function updateUsername(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
        ]);

        $user->username = $data['username'];
        $user->save();

        return back()->with('success', 'Username berhasil diperbarui.');
    }

    /** Ganti password akun yang sedang login. */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 10 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->password = $data['password'];
        $user->save();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}

