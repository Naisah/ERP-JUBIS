<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $type = $request->input('type', 'client'); // Default to client list
        if ($type === 'staff') {
            $query->where('role', '!=', 'client');
        } else {
            $query->where('role', 'client');
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->search,
                'role' => $request->role,
                'type' => $type
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['super_admin', 'admin', 'sales', 'finance', 'purchasing', 'warehouse', 'client'])],
            'company_name' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        if ($validated['role'] === 'client') {
            $validated['credit_status'] = 'approved';
        }

        User::create($validated);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['super_admin', 'admin', 'sales', 'finance', 'purchasing', 'warehouse', 'client'])],
            'company_name' => 'nullable|string|max:255',
            'credit_status' => 'nullable|string|in:pending,approved,suspended',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting oneself
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully.');
    }
}
