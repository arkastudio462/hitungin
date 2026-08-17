<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use Illuminate\Http\Request;

class RecurringTransactionController extends Controller
{
    public function index(Request $request)
    {
        $recurring = $request->user()
            ->recurringTransactions()
            ->with(['category', 'account'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($recurring);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'frequency' => ['required', 'in:daily,weekly,monthly,yearly'],
            'interval' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $category = $request->user()->categories()->findOrFail($validated['category_id']);
        if ($category->type !== $validated['type']) {
            return response()->json(['message' => 'Category type mismatch.'], 422);
        }

        if (isset($validated['account_id'])) {
            $request->user()->accounts()->findOrFail($validated['account_id']);
        }

        $validated['user_id'] = $request->user()->id;
        $validated['next_run_date'] = $validated['start_date'];

        $recurring = RecurringTransaction::create($validated);
        $recurring->load(['category', 'account']);

        return response()->json($recurring, 201);
    }

    public function update(Request $request, RecurringTransaction $recurring)
    {
        if ((int) $recurring->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'type' => ['sometimes', 'in:income,expense'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'frequency' => ['sometimes', 'in:daily,weekly,monthly,yearly'],
            'interval' => ['sometimes', 'integer', 'min:1'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (isset($validated['category_id']) || isset($validated['type'])) {
            $categoryId = $validated['category_id'] ?? $recurring->category_id;
            $type = $validated['type'] ?? $recurring->type;
            $category = $request->user()->categories()->findOrFail($categoryId);
            if ($category->type !== $type) {
                return response()->json(['message' => 'Category type mismatch.'], 422);
            }
        }

        $recurring->update($validated);
        $recurring->load(['category', 'account']);

        return response()->json($recurring);
    }

    public function destroy(Request $request, RecurringTransaction $recurring)
    {
        if ((int) $recurring->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $recurring->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
