<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $budgets = $request->user()
            ->budgets()
            ->with('category')
            ->when($request->period, fn ($q, $period) => $q->where('period', $period))
            ->orderByDesc('created_at')
            ->get();

        $budgets->each(function ($budget) {
            $budget->spent = $budget->user->transactions()
                ->where('category_id', $budget->category_id)
                ->where('type', 'expense')
                ->whereBetween('date', [$budget->start_date, $budget->end_date])
                ->sum('amount');
        });

        return response()->json($budgets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'period' => ['required', 'in:monthly,yearly'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $category = $request->user()->categories()->findOrFail($validated['category_id']);

        if ($category->type !== 'expense') {
            return response()->json(['message' => 'Budget category must be an expense type.'], 422);
        }

        $budget = $request->user()->budgets()->create($validated);
        $budget->load('category');
        $budget->spent = 0;

        return response()->json($budget, 201);
    }

    public function update(Request $request, Budget $budget)
    {
        if ((int) $budget->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'period' => ['sometimes', 'in:monthly,yearly'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        if (isset($validated['category_id'])) {
            $category = $request->user()->categories()->findOrFail($validated['category_id']);
            if ($category->type !== 'expense') {
                return response()->json(['message' => 'Budget category must be an expense type.'], 422);
            }
        }

        $budget->update($validated);
        $budget->load('category');
        $budget->spent = $budget->user->transactions()
            ->where('category_id', $budget->category_id)
            ->where('type', 'expense')
            ->whereBetween('date', [$budget->start_date, $budget->end_date])
            ->sum('amount');

        return response()->json($budget);
    }

    public function destroy(Request $request, Budget $budget)
    {
        if ((int) $budget->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $budget->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
