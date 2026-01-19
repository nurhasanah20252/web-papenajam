<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\BudgetCategory;
use App\Models\BudgetTransparency;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BudgetTransparencyController extends Controller
{
    /**
     * Display a listing of budget transparency entries.
     */
    public function index(Request $request): Response
    {
        $query = BudgetTransparency::query()
            ->with(['author'])
            ->published()
            ->latest('year')
            ->latest('published_at');

        // Filter by year
        if ($request->has('year') && $request->filled('year')) {
            $query->byYear((int) $request->input('year'));
        }

        // Filter by category
        if ($request->has('category') && $request->filled('category')) {
            $category = BudgetCategory::tryFrom($request->input('category'));
            if ($category) {
                $query->byCategory($category);
            }
        }

        // Search
        if ($request->has('search') && $request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Paginate results
        $budgets = $query->paginate(12);

        // Get available years for filter
        $availableYears = BudgetTransparency::query()
            ->published()
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return Inertia::render('budget-transparency/index', [
            'budgets' => $budgets,
            'availableYears' => $availableYears,
            'filters' => [
                'year' => $request->input('year'),
                'category' => $request->input('category'),
                'search' => $request->input('search'),
            ],
        ]);
    }
}
