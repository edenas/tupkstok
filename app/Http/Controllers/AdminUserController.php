<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private const AVAILABLE_ROLES = [
        'administrator',
        'editor',
        'user',
    ];

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
            'availableRoles' => self::AVAILABLE_ROLES,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->createValidationRules(), $this->passwordValidationMessages());

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function showEditForm(User $user)
    {
        return view('admin.users-edit', [
            'availableRoles' => self::AVAILABLE_ROLES,
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate($this->updateValidationRules($user), $this->passwordValidationMessages());

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    /**
     * Get validation rules for creating a user.
     *
     * @return array<string, mixed>
     */
    private function createValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
            'role' => [
                'required',
                Rule::in(self::AVAILABLE_ROLES),
            ],
        ];
    }

    /**
     * Get validation rules for updating a user.
     *
     * @return array<string, mixed>
     */
    private function updateValidationRules(User $user): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|string|required_with:password',
            'role' => [
                'required',
                Rule::in(self::AVAILABLE_ROLES),
            ],
        ];
    }

    /**
     * Get validation messages for password confirmation.
     *
     * @return array<string, string>
     */
    private function passwordValidationMessages(): array
    {
        return [
            'password.required_with' => 'Password and confirmation password must match.',
            'password.required' => 'Password is required.',
            'password_confirmation.required_with' => 'Password and confirmation password must match.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password.confirmed' => 'Password and confirmation password must match.',
        ];
    }
}
