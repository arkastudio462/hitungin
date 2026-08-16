<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $period = $request->get('period', 'month');
        $now = now();

        match ($period) {
            'week' => $start = $now->copy()->startOfWeek(),
            'month' => $start = $now->copy()->startOfMonth(),
            'year' => $start = $now->copy()->startOfYear(),
            default => $start = $now->copy()->startOfMonth(),
        };

        $startDate = $start->toDateString();
        $endDate = $now->endOfDay()->toDateTimeString();

        $totalIncome = $user->transactions()
            ->where('type', 'income')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->sum('amount');

        $totalExpense = $user->transactions()
            ->where('type', 'expense')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->sum('amount');

        $recentTransactions = $user->transactions()
            ->with(['category', 'account', 'tags'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $categoryBreakdown = $user->transactions()
            ->where('transactions.type', 'expense')
            ->where('transactions.date', '>=', $startDate)
            ->where('transactions.date', '<=', $endDate)
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', 'categories.color', 'categories.icon')
            ->selectRaw('SUM(transactions.amount) as total')
            ->groupBy('categories.id', 'categories.name', 'categories.color', 'categories.icon')
            ->orderByDesc('total')
            ->get();

        $totalBalance = $user->accounts()
            ->where('is_active', true)
            ->sum('balance');

        return response()->json([
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
            'total_balance' => $totalBalance,
            'recent_transactions' => $recentTransactions,
            'category_breakdown' => $categoryBreakdown,
            'period' => $period,
        ]);
    }
}
