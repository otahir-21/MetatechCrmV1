<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Client;
use App\Models\User;
use App\Services\DealService;
use Illuminate\Http\Request;

class DealController extends Controller
{
    protected DealService $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    /**
     * Display Kanban board with deals.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get deals grouped by stage
        $dealsByStage = $this->dealService->getDealsByStage($user);
        
        // Get statistics
        $statistics = $this->dealService->getStatistics($user);
        
        // Get all sales users for assignment
        $salesUsers = User::where('is_metatech_employee', true)
            ->whereIn('role', ['user', 'admin', 'super_admin'])
            ->get();

        return view('internal.deals.index', compact('dealsByStage', 'statistics', 'salesUsers'));
    }

    /**
     * Show form to create new deal.
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $salesUsers = User::where('is_metatech_employee', true)
            ->whereIn('role', ['user', 'admin', 'super_admin'])
            ->get();

        return view('internal.deals.create', compact('clients', 'salesUsers'));
    }

    /**
     * Store new deal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'stage' => 'required|in:new_lead,contacted,qualified,proposal_sent,negotiation,won,lost',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'required|exists:users,id',
            'expected_close_date' => 'nullable|date',
            'lead_source' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $deal = $this->dealService->createDeal($validated, auth()->user());

        return redirect()->route('internal.deals.index')
            ->with('success', 'Deal created successfully!');
    }

    /**
     * Show specific deal.
     */
    public function show(Deal $deal)
    {
        $this->authorize('view', $deal);
        
        $deal->load(['client', 'assignedUser']);
        
        return view('internal.deals.show', compact('deal'));
    }

    /**
     * Show form to edit deal.
     */
    public function edit(Deal $deal)
    {
        $this->authorize('update', $deal);
        
        $clients = Client::orderBy('name')->get();
        $salesUsers = User::where('is_metatech_employee', true)
            ->whereIn('role', ['user', 'admin', 'super_admin'])
            ->get();

        return view('internal.deals.edit', compact('deal', 'clients', 'salesUsers'));
    }

    /**
     * Update deal.
     */
    public function update(Request $request, Deal $deal)
    {
        $this->authorize('update', $deal);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'value' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'stage' => 'required|in:new_lead,contacted,qualified,proposal_sent,negotiation,won,lost',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'required|exists:users,id',
            'expected_close_date' => 'nullable|date',
            'lead_source' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'lost_reason' => 'nullable|string',
        ]);

        $deal = $this->dealService->updateDeal($deal, $validated);

        return redirect()->route('internal.deals.index')
            ->with('success', 'Deal updated successfully!');
    }

    /**
     * Delete deal.
     */
    public function destroy(Deal $deal)
    {
        $this->authorize('delete', $deal);
        
        $this->dealService->deleteDeal($deal);

        return redirect()->route('internal.deals.index')
            ->with('success', 'Deal deleted successfully!');
    }

    /**
     * Move deal to different stage (for drag-and-drop).
     */
    public function updateStage(Request $request, Deal $deal)
    {
        $this->authorize('update', $deal);
        
        $validated = $request->validate([
            'stage' => 'required|in:new_lead,contacted,qualified,proposal_sent,negotiation,won,lost',
        ]);

        $this->dealService->moveDealToStage($deal, $validated['stage']);

        return response()->json([
            'success' => true,
            'message' => 'Deal moved successfully!',
            'deal' => $deal->fresh(['client', 'assignedUser']),
        ]);
    }
}
