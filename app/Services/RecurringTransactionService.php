<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use Carbon\Carbon;

class RecurringTransactionService
{
    public function processDueRecurringTransactions(): void
    {
        $dueRecurring = RecurringTransaction::where('is_active', true)
            ->where('next_run_date', '<=', Carbon::today())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            })
            ->with(['category', 'account'])
            ->get();

        foreach ($dueRecurring as $recurring) {
            $this->createTransaction($recurring);
            $this->updateNextRunDate($recurring);
        }
    }

    private function createTransaction(RecurringTransaction $recurring): void
    {
        $transaction = $recurring->user->transactions()->create([
            'category_id' => $recurring->category_id,
            'account_id' => $recurring->account_id,
            'type' => $recurring->type,
            'amount' => $recurring->amount,
            'description' => $recurring->description ?? '[Otomatis] '.$recurring->category->name,
            'date' => $recurring->next_run_date,
        ]);

        if ($recurring->account_id) {
            $account = $recurring->account;
            if ($recurring->type === 'income') {
                $account->increment('balance', $recurring->amount);
            } else {
                $account->decrement('balance', $recurring->amount);
            }
        }
    }

    private function updateNextRunDate(RecurringTransaction $recurring): void
    {
        $next = match ($recurring->frequency) {
            'daily' => $recurring->next_run_date->addDays($recurring->interval),
            'weekly' => $recurring->next_run_date->addWeeks($recurring->interval),
            'monthly' => $recurring->next_run_date->addMonths($recurring->interval),
            'yearly' => $recurring->next_run_date->addYears($recurring->interval),
            default => $recurring->next_run_date->addMonth(),
        };

        if ($recurring->end_date && $next->gt($recurring->end_date)) {
            $recurring->update(['is_active' => false, 'next_run_date' => $next]);
        } else {
            $recurring->update(['next_run_date' => $next]);
        }
    }
}
