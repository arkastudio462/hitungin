<?php

namespace App\Services;

use App\Models\User;

class BudgetAlertService
{
    public function check(User $user): void
    {
        $budgets = $user->budgets()
            ->with('category')
            ->where('end_date', '>=', now()->toDateString())
            ->get();

        foreach ($budgets as $budget) {
            $spent = $user->transactions()
                ->where('category_id', $budget->category_id)
                ->where('type', 'expense')
                ->whereBetween('date', [$budget->start_date, $budget->end_date])
                ->sum('amount');

            $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;

            if ($percentage >= 90) {
                $this->createIfNotExists(
                    $user,
                    'budget_critical',
                    'Anggaran Kritis!',
                    sprintf('%s sudah terpakai %.0f%% (%s dari %s).', $budget->category->name, $percentage, number_format($spent, 0, ',', '.'), number_format($budget->amount, 0, ',', '.')),
                    ['budget_id' => $budget->id, 'percentage' => round($percentage)]
                );
            } elseif ($percentage >= 70) {
                $this->createIfNotExists(
                    $user,
                    'budget_warning',
                    'Anggaran Mendekati Batas',
                    sprintf('%s sudah terpakai %.0f%% (%s dari %s).', $budget->category->name, $percentage, number_format($spent, 0, ',', '.'), number_format($budget->amount, 0, ',', '.')),
                    ['budget_id' => $budget->id, 'percentage' => round($percentage)]
                );
            }
        }
    }

    private function createIfNotExists(User $user, string $type, string $title, string $message, array $data = []): void
    {
        $exists = $user->notifications()
            ->where('type', $type)
            ->whereDate('created_at', now()->toDateString())
            ->where('data->budget_id', $data['budget_id'] ?? null)
            ->exists();

        if (! $exists) {
            $user->notifications()->create([
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
        }
    }
}
