<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with(['pages' => fn ($query) => $query->orderBy('id')])
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load([
            'pages' => fn ($query) => $query
                ->withCount(['categories', 'items', 'links'])
                ->orderBy('id'),
        ]);

        return view('admin.users.show', [
            'managedUser' => $user,
            'page' => $user->pages->first(),
        ]);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'لا يمكنك إيقاف حسابك الإداري الحالي.');

        $data = $request->validate([
            'suspended_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_reason' => $data['suspended_reason'] ?? null,
        ]);

        return back()->with('success', 'تم إيقاف حساب المستخدم.');
    }

    public function activate(User $user): RedirectResponse
    {
        $user->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspended_reason' => null,
        ]);

        return back()->with('success', 'تمت إعادة تفعيل حساب المستخدم.');
    }
}
