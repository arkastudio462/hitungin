<?php

namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use Illuminate\Http\Request;

class SavingsGoalController extends Controller
{
    public function index(Request $request)
    {
        $goals = $request->user()
            ->savingsGoals()
            ->with('account')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($goals);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'target_date' => ['nullable', 'date', 'after:today'],
            'icon' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $validated['user_id'] = $request->user()->id;
        $validated['current_amount'] = 0;

        $goal = SavingsGoal::create($validated);
        $goal->load('account');

        return response()->json($goal, 201);
    }

    public function update(Request $request, SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'target_amount' => ['sometimes', 'numeric', 'min:1'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'target_date' => ['nullable', 'date'],
            'icon' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:7'],
            'current_amount' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $savingsGoal->update($validated);
        $savingsGoal->load('account');

        return response()->json($savingsGoal);
    }

    public function destroy(Request $request, SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $savingsGoal->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    public function deposit(Request $request, SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $savingsGoal->increment('current_amount', $validated['amount']);

        if ($savingsGoal->current_amount >= $savingsGoal->target_amount) {
            $savingsGoal->update(['is_completed' => true]);
        }

        $savingsGoal->load('account');

        return response()->json($savingsGoal);
    }
}
