<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\BudgetAlertService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = $request->user()
            ->transactions()
            ->with(['category', 'account', 'tags'])
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->account_id, fn ($q, $id) => $q->where('account_id', $id))
            ->when($request->tag_id, fn ($q, $id) => $q->whereHas('tags', fn ($tq) => $tq->where('tags.id', $id)))
            ->when($request->search, fn ($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->when($request->from, fn ($q, $date) => $q->where('date', '>=', $date))
            ->when($request->to, fn ($q, $date) => $q->where('date', '<=', $date))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ]);

        $category = $request->user()->categories()->findOrFail($validated['category_id']);

        if ($category->type !== $validated['type']) {
            return response()->json(['message' => 'Category type mismatch.'], 422);
        }

        if (isset($validated['account_id'])) {
            $request->user()->accounts()->findOrFail($validated['account_id']);
        }

        $tagIds = $validated['tags'] ?? [];
        unset($validated['tags']);

        $transaction = $request->user()->transactions()->create($validated);

        if ($tagIds) {
            $transaction->tags()->sync($tagIds);
        }

        $transaction->load(['category', 'account', 'tags']);

        if ($validated['type'] === 'expense') {
            app(BudgetAlertService::class)->check($request->user());
        }

        if ($validated['type'] === 'expense' && isset($validated['account_id'])) {
            $transaction->account->decrement('balance', $validated['amount']);
        } elseif ($validated['type'] === 'income' && isset($validated['account_id'])) {
            $transaction->account->increment('balance', $validated['amount']);
        }

        return response()->json($transaction, 201);
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'type' => ['sometimes', 'in:income,expense'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['sometimes', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ]);

        if (isset($validated['category_id']) || isset($validated['type'])) {
            $categoryId = $validated['category_id'] ?? $transaction->category_id;
            $type = $validated['type'] ?? $transaction->type;

            $category = $request->user()->categories()->findOrFail($categoryId);

            if ($category->type !== $type) {
                return response()->json(['message' => 'Category type mismatch.'], 422);
            }
        }

        if (isset($validated['account_id'])) {
            $request->user()->accounts()->findOrFail($validated['account_id']);
        }

        if (array_key_exists('tags', $validated)) {
            $transaction->tags()->sync($validated['tags'] ?? []);
            unset($validated['tags']);
        }

        $transaction->update($validated);
        $transaction->load(['category', 'account', 'tags']);

        return response()->json($transaction);
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $transaction->tags()->detach();
        $transaction->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
