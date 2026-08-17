<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = $request->user()
            ->accounts()
            ->orderBy('name')
            ->get();

        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:cash,bank,e-wallet,credit'],
            'balance' => ['nullable', 'numeric'],
            'icon' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['balance'] = $validated['balance'] ?? 0;

        $account = Account::create($validated);

        return response()->json($account, 201);
    }

    public function update(Request $request, Account $account)
    {
        if ((int) $account->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:cash,bank,e-wallet,credit'],
            'balance' => ['sometimes', 'numeric'],
            'icon' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:7'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $account->update($validated);

        return response()->json($account);
    }

    public function destroy(Request $request, Account $account)
    {
        if ((int) $account->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($account->transactions()->exists()) {
            return response()->json(['message' => 'Cannot delete account with existing transactions.'], 422);
        }

        $account->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
