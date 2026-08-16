<?php

namespace App\Http\Controllers;

use App\Services\BudgetAlertService;
use App\Services\ReceiptScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    public function scan(Request $request)
    {
        $request->validate([
            'receipt' => ['required', 'file', 'image', 'max:10240'],
        ]);

        $path = $request->file('receipt')->store('receipts', 'local');

        try {
            $result = app(ReceiptScanService::class)->scan($path);

            return response()->json([
                'receipt_path' => $path,
                'parsed' => $result,
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);

            return response()->json([
                'message' => 'Gagal memproses struk: '.$e->getMessage(),
            ], 422);
        }
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'receipt_path' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'items' => ['nullable', 'array'],
        ]);

        $category = $request->user()->categories()->findOrFail($validated['category_id']);

        if ($category->type !== $validated['type']) {
            return response()->json(['message' => 'Category type mismatch.'], 422);
        }

        if (isset($validated['account_id'])) {
            $request->user()->accounts()->findOrFail($validated['account_id']);
        }

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        if (! empty($items) && empty($validated['description'])) {
            $itemNames = array_column($items, 'name');
            $validated['description'] = implode(', ', array_slice($itemNames, 0, 3));
            if (count($itemNames) > 3) {
                $validated['description'] .= ' dll.';
            }
        }

        $transaction = $request->user()->transactions()->create($validated);
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
}
