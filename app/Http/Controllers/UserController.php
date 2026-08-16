<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with('roles:id,name')
            ->when($request->string('q')->toString(), fn ($query, $term) => $query->where(
                fn ($inner) => $inner->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")
            ))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'active' => $request->boolean('active', true),
        ]);

        $user->assignRole('seller');

        return redirect()->route('users.index')->with('success', __('messages.flash.created'));
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ];

        // لا يمكن إلغاء تفعيل حساب المدير.
        if (! $user->isAdmin()) {
            $data['active'] = $request->boolean('active');
        } elseif (! $request->boolean('active', true)) {
            return back()->with('error', __('messages.users.cannot_deactivate_admin'));
        }

        if ($request->validated('password')) {
            $data['password'] = $request->validated('password');
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', __('messages.flash.updated'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', __('messages.users.cannot_delete_admin'));
        }

        if ($user->id === $request->user()->id) {
            return back()->with('error', __('messages.users.cannot_delete_self'));
        }

        if ($user->sales()->exists()) {
            return back()->with('error', __('messages.users.has_sales'));
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', __('messages.flash.deleted'));
    }
}
