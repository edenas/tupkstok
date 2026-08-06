<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::query()
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withPath(route('admin.users'));

        return view('admin.users', compact('users'));
    }

    /**
     * Show the form for creating a user.
     */
    public function showCreateForm()
    {
        return view('admin.users-create', [
            'availableRoles' => User::AVAILABLE_ROLES,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(SaveUserRequest $request)
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users')->with('success', 'Vartotojas sukurtas sėkmingai.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function showEditForm(User $user)
    {
        return view('admin.users-edit', [
            'availableRoles' => User::AVAILABLE_ROLES,
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(SaveUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if ($request->user()->is($user) && $user->role === 'administrator' && $validated['role'] !== 'administrator') {
            return redirect()->route('admin.users')->with('error', 'Negalite pašalinti savo administratoriaus rolės.');
        }

        if ($this->wouldRemoveLastAdministrator($user, $validated['role'])) {
            return redirect()->route('admin.users')->with('error', 'Negalite pašalinti paskutinės administratoriaus paskyros.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'Vartotojas atnaujintas sėkmingai.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return redirect()->route('admin.users')->with('error', 'Negalite ištrinti savo paskyros.');
        }

        if ($user->role === 'administrator' && $this->administratorCount() <= 1) {
            return redirect()->route('admin.users')->with('error', 'Negalite ištrinti paskutinės administratoriaus paskyros.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Vartotojas ištrintas sėkmingai.');
    }

    private function wouldRemoveLastAdministrator(User $user, string $newRole): bool
    {
        return $user->role === 'administrator'
            && $newRole !== 'administrator'
            && $this->administratorCount() <= 1;
    }

    private function administratorCount(): int
    {
        return User::query()->where('role', 'administrator')->count();
    }
}
