<?php

namespace App\Http\Controllers;

use App\Models\Request as MaterialRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\RequestItem;
use App\Models\Material;
use App\Models\Category;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Date range filter
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : null;
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : null;

        // Get selected category IDs from the request (array or null)
        $selectedCategoryIds = $request->input('category_ids', []);
        if (!is_array($selectedCategoryIds)) {
            $selectedCategoryIds = [$selectedCategoryIds];
        }
        $selectedCategoryIds = array_filter($selectedCategoryIds); // Remove empty

        // Get all categories for the filter dropdown
        $allCategories = Category::select('id', 'name')->orderBy('name')->get();

        $requestsQuery = MaterialRequest::with(['user', 'requestItems.material.category'])
            ->orderByDesc('requested_at');
        if ($startDate) $requestsQuery->where('requested_at', '>=', $startDate);
        if ($endDate) $requestsQuery->where('requested_at', '<=', $endDate);
        $requests = $requestsQuery->get();

        $total = $requests->count();
        $pending = $requests->where('status', 'pendente')->count();
        $reserved = $requests->where('status', 'reservado')->count();
        $overdue = $requests->filter(function ($req) {
            return $req->status === 'reservado' && $req->requestItems->where('due_date', '<', now())->where('returned', false)->count() > 0;
        })->count();
        $ongoing = $requests->filter(function ($req) {
            return $req->status === 'reservado' && $req->requestItems->where('returned', false)->count() > 0;
        })->count();

        $recent = $requests->take(10)->map(function ($req) {
            return [
                'id' => $req->id,
                'student' => $req->user->name,
                'status' => $req->status,
                'requested_at' => $req->requested_at ? Carbon::parse($req->requested_at)->format('d/m/Y H:i') : null,
            ];
        });

        // --- Chart 1: Requests per month (last 6 months) ---
        $currentYear = Carbon::now()->year;
        $months = collect(range(0, 5))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        })->reverse()->values();
        $requestsPerMonth = $months->map(function ($month) use ($requests, $currentYear) {
            $date = Carbon::createFromFormat('Y-m', $month);
            $label = $date->year === $currentYear
                ? $date->translatedFormat('F')
                : $date->translatedFormat('M y');
            return [
                'month' => $label,
                'count' => $requests->where('requested_at', '>=', $date->startOfMonth())
                    ->where('requested_at', '<=', $date->endOfMonth())
                    ->count(),
            ];
        });

        // --- Chart 2: Requests per category (all time or filtered) ---
        $categories = Category::with('materials');
        if (!empty($selectedCategoryIds)) {
            $categories = $categories->whereIn('id', $selectedCategoryIds);
        }
        $categories = $categories->get();

        $categoryCounts = $categories->map(function ($cat) use ($requests) {
            $count = 0;
            foreach ($requests as $req) {
                foreach ($req->requestItems as $item) {
                    if ($item->material && $item->material->category && $item->material->category->id === $cat->id) {
                        $count += $item->quantity;
                    }
                }
            }
            return [
                'category' => $cat->name,
                'count' => $count,
            ];
        })->filter(fn($row) => $row['count'] > 0)->values();

        // --- Chart 3: Requests by status (pie/donut) ---
        $statusLabels = ['pendente', 'reservado', 'devolvido', 'cancelado'];
        $statusCounts = collect($statusLabels)->map(function ($status) use ($requests) {
            return $requests->where('status', $status)->count();
        });

        // --- Chart 4: Top requested materials (bar) ---
        $materialCounts = [];
        foreach ($requests as $req) {
            foreach ($req->requestItems as $item) {
                if ($item->material) {
                    $materialName = $item->material->name;
                    if (!isset($materialCounts[$materialName])) {
                        $materialCounts[$materialName] = 0;
                    }
                    $materialCounts[$materialName] += $item->quantity;
                }
            }
        }
        $topMaterials = collect($materialCounts)
            ->sortDesc()
            ->take(7)
            ->map(function ($count, $name) {
                return ['material' => $name, 'count' => $count];
            })->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $total,
                'pending' => $pending,
                'reserved' => $reserved,
                'overdue' => $overdue,
                'ongoing' => $ongoing,
            ],
            'recent' => $recent,
            'charts' => [
                'requests_per_month' => [
                    'labels' => $requestsPerMonth->pluck('month'),
                    'data' => $requestsPerMonth->pluck('count'),
                ],
                'requests_per_category' => [
                    'labels' => $categoryCounts->pluck('category'),
                    'data' => $categoryCounts->pluck('count'),
                ],
                'requests_by_status' => [
                    'labels' => $statusLabels,
                    'data' => $statusCounts,
                ],
                'top_materials' => [
                    'labels' => $topMaterials->pluck('material'),
                    'data' => $topMaterials->pluck('count'),
                ],
            ],
            'filters' => [
                'start_date' => $startDate ? $startDate->toDateString() : null,
                'end_date' => $endDate ? $endDate->toDateString() : null,
                'category_ids' => $selectedCategoryIds,
            ],
            'categories' => $allCategories,
        ]);
    }
}
