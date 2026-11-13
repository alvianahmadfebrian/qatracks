<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\BugNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function authorizeAdmin(): void
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized.');
        }
    }

    public function index()
    {
        $this->authorizeAdmin();

        $users = User::orderBy('created_at', 'desc')->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:super_admin,support_dev,developer'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $newUser = User::create($validated);

        $newUser->notify(new BugNotification(
            title: 'Selamat Datang di QATrack!',
            message: "Akun Anda telah aktif dengan role: {$newUser->role}.",
            type: 'system',
            icon: 'verified_user',
            badgeColor: 'primary'
        ));

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorizeAdmin();

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:super_admin,support_dev,developer'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorizeAdmin();

        if ($user->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
