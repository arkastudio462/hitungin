<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function monthExpr(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%m', {$column})"
            : "DATE_FORMAT({$column}, '%m')";
    }

    private function yearMonthExpr(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    public function summary(Request $request)
    {
        $user = $request->user();
        $year = $request->get('year', now()->year);

        $monthly = $user->transactions()
            ->whereYear('date', $year)
            ->selectRaw($this->monthExpr('date').' as month')
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'year' => (int) $year,
            'monthly' => $monthly,
        ]);
    }

    public function byCategory(Request $request)
    {
        $user = $request->user();
        $type = $request->get('type', 'expense');
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $breakdown = $user->transactions()
            ->where('transactions.type', $type)
            ->whereMonth('transactions.date', $month)
            ->whereYear('transactions.date', $year)
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.name', 'categories.color', 'categories.icon')
            ->selectRaw('SUM(transactions.amount) as total')
            ->groupBy('categories.id', 'categories.name', 'categories.color', 'categories.icon')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'type' => $type,
            'month' => (int) $month,
            'year' => (int) $year,
            'categories' => $breakdown,
        ]);
    }

    public function trend(Request $request)
    {
        $user = $request->user();
        $months = (int) $request->get('months', 6);

        $startDate = now()->subMonths($months - 1)->startOfMonth();

        $trend = $user->transactions()
            ->where('date', '>=', $startDate)
            ->selectRaw($this->yearMonthExpr('date').' as month')
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'months' => $months,
            'trend' => $trend,
        ]);
    }
}
