<?php

namespace App\Http\Controllers;

use App\Models\NotificationForward;
use App\Services\BudgetAlertService;
use App\Services\NotificationParseService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NotificationForwardController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        if (! $user->auto_detect_enabled) {
            return response()->json(['message' => 'Auto detection disabled.'], 403);
        }

        $hash = Str::hash($validated['package_name'].$validated['message']);

        $exists = $user->notificationForwards()
            ->where('created_at', '>=', now()->subMinutes(5))
            ->whereRaw('hex(package_name || message) = hex(?)', [$validated['package_name'].$validated['message']])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Duplicate notification.'], 409);
        }

        $forward = $user->notificationForwards()->create([
            'package_name' => $validated['package_name'],
            'title' => $validated['title'] ?? null,
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        try {
            $parsed = app(NotificationParseService::class)->parse($forward);

            if (($parsed['type'] ?? '') === 'ignore') {
                $forward->markIgnored();
            } else {
                $forward->markParsed($parsed);
            }

            return response()->json([
                'id' => $forward->id,
                'status' => $forward->status,
                'parsed' => $parsed,
            ], 201);
        } catch (\Throwable $e) {
            $forward->update(['status' => 'pending']);

            return response()->json([
                'id' => $forward->id,
                'status' => 'pending',
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    public function index(Request $request)
    {
        $forwards = $request->user()
            ->notificationForwards()
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($forwards);
    }

    public function confirm(Request $request, NotificationForward $forward)
    {
        if ((int) $forward->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($forward->status !== 'parsed') {
            return response()->json(['message' => 'Notification already processed.'], 422);
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'type' => ['required', 'in:income,expense'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
        ]);

        $category = $request->user()->categories()->findOrFail($validated['category_id']);

        if ($category->type !== $validated['type']) {
            return response()->json(['message' => 'Category type mismatch.'], 422);
        }

        if (isset($validated['account_id'])) {
            $request->user()->accounts()->findOrFail($validated['account_id']);
        }

        $transaction = $request->user()->transactions()->create([
            'category_id' => $validated['category_id'],
            'account_id' => $validated['account_id'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'date' => $validated['date'],
            'source' => $forward->package_name,
            'auto_detected' => true,
        ]);

        $transaction->load(['category', 'account', 'tags']);

        $forward->markConfirmed($transaction);

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

    public function ignore(Request $request, NotificationForward $forward)
    {
        if ((int) $forward->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $forward->markIgnored();

        return response()->json(['message' => 'Notification ignored.']);
    }

    public function pendingCount(Request $request)
    {
        $count = $request->user()
            ->notificationForwards()
            ->pending()
            ->count();

        return response()->json(['count' => $count]);
    }
}
