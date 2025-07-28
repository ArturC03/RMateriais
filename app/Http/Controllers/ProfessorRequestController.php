<?php

namespace App\Http\Controllers;

use App\Models\Request as MaterialRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class ProfessorRequestController extends Controller
{
    /**
     * Display a listing of pending requests for professors
     */
    public function index()
    {
        $pendingRequests = MaterialRequest::with(['user', 'requestItems.material.category'])
            ->where('status', 'pendente')
            ->orderBy('requested_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'student_name' => $request->user->name,
                    'student_email' => $request->user->email,
                    'requested_at' => $request->requested_at->format('d/m/Y H:i'),
                    'items' => $request->requestItems->map(function ($item) {
                        return [
                            'material_name' => $item->material->name,
                            'quantity' => $item->quantity,
                            'category' => $item->material->category->name,
                        ];
                    }),
                    'total_items' => $request->requestItems->count(),
                ];
            });

        $reservedRequests = MaterialRequest::with(['user', 'requestItems.material.category'])
            ->where('status', 'reservado')
            ->orderBy('approved_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'student_name' => $request->user->name,
                    'student_email' => $request->user->email,
                    'approved_at' => $request->approved_at->format('d/m/Y H:i'),
                    'due_date' => $request->requestItems->first()?->due_date->format('d/m/Y'),
                    'items' => $request->requestItems->map(function ($item) {
                        return [
                            'material_name' => $item->material->name,
                            'quantity' => $item->quantity,
                            'category' => $item->material->category->name,
                            'returned' => $item->returned,
                        ];
                    }),
                    'total_items' => $request->requestItems->count(),
                    'returned_items' => $request->requestItems->where('returned', true)->count(),
                ];
            });

        return Inertia::render('Dashboard/Requests', [
            'pendingRequests' => $pendingRequests,
            'reservedRequests' => $reservedRequests,
        ]);
    }

    /**
     * Show the details of a specific request
     */
    public function show(MaterialRequest $request)
    {
        $request->load(['user', 'requestItems.material.category']);

        $requestData = [
            'id' => $request->id,
            'student_name' => $request->user->name,
            'student_email' => $request->user->email,
            'status' => $request->status,
            'requested_at' => $request->requested_at?->format('d/m/Y H:i'),
            'approved_at' => $request->approved_at?->format('d/m/Y H:i'),
            'due_date' => $request->requestItems->first()?->due_date->format('d/m/Y'),
            'items' => $request->requestItems->map(function ($item) {
                return [
                    'material_name' => $item->material->name,
                    'quantity' => $item->quantity,
                    'category' => $item->material->category->name,
                    'returned' => $item->returned,
                    'reserved_at' => $item->reserved_at?->format('d/m/Y H:i'),
                ];
            }),
        ];

        return Inertia::render('Dashboard/RequestDetail', [
            'request' => $requestData,
        ]);
    }

    /**
     * Confirm a request and reserve the materials
     * This happens after the professor gives the materials to the student
     */
    public function confirm(Request $httpRequest): RedirectResponse
    {
        try {
            $request = MaterialRequest::findOrFail($httpRequest->input('request_id'));

            if ($request->status !== 'pendente') {
                return back()->withErrors(['error' => 'Apenas requisições pendentes podem ser confirmadas']);
            }

            $request->confirmAndReserve();

            return back()->with('success', 'Requisição confirmada e materiais reservados com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark a request as returned
     */
    public function markAsReturned(Request $httpRequest): RedirectResponse
    {
        try {
            $request = MaterialRequest::findOrFail($httpRequest->input('request_id'));

            if ($request->status !== 'reservado') {
                return back()->withErrors(['error' => 'Apenas requisições reservadas podem ser marcadas como devolvidas']);
            }

            $request->markAsReturned();

            return back()->with('success', 'Requisição marcada como devolvida com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel a request
     */
    public function cancel(Request $httpRequest): RedirectResponse
    {
        try {
            $request = MaterialRequest::findOrFail($httpRequest->input('request_id'));

            if (!in_array($request->status, ['rascunho', 'pendente'])) {
                return back()->withErrors(['error' => 'Apenas rascunhos ou requisições pendentes podem ser canceladas']);
            }

            $request->cancel();

            return back()->with('success', 'Requisição cancelada com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
