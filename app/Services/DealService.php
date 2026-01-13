<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DealService
{
    /**
     * Get all deals grouped by stage (for Kanban board).
     */
    public function getDealsByStage(User $user): array
    {
        $query = Deal::with(['client', 'assignedUser']);

        // Filter by permissions: Sales agents see only their deals
        if ($user->role === 'user' && $user->is_metatech_employee) {
            $query->where('assigned_to', $user->id);
        }

        $deals = $query->orderBy('created_at', 'desc')->get();

        // Group by stage
        $stages = [
            'new_lead' => [],
            'contacted' => [],
            'qualified' => [],
            'proposal_sent' => [],
            'negotiation' => [],
            'won' => [],
            'lost' => [],
        ];

        foreach ($deals as $deal) {
            $stages[$deal->stage][] = $deal;
        }

        return $stages;
    }

    /**
     * Get all deals (with filters).
     */
    public function getAllDeals(User $user, array $filters = [])
    {
        $query = Deal::with(['client', 'assignedUser']);

        // Apply permission filters
        if ($user->role === 'user' && $user->is_metatech_employee) {
            $query->where('assigned_to', $user->id);
        }

        // Apply additional filters
        if (isset($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        return $query->latest()->get();
    }

    /**
     * Create a new deal.
     */
    public function createDeal(array $data, User $user): Deal
    {
        return DB::transaction(function () use ($data, $user) {
            // Set defaults
            $data['stage'] = $data['stage'] ?? 'new_lead';
            $data['priority'] = $data['priority'] ?? 'medium';
            $data['currency'] = $data['currency'] ?? 'USD';
            
            // If no assigned_to, assign to creator
            if (!isset($data['assigned_to'])) {
                $data['assigned_to'] = $user->id;
            }

            return Deal::create($data);
        });
    }

    /**
     * Update existing deal.
     */
    public function updateDeal(Deal $deal, array $data): Deal
    {
        // Handle stage changes
        if (isset($data['stage']) && $data['stage'] !== $deal->stage) {
            if ($data['stage'] === 'won' && !$deal->won_at) {
                $data['won_at'] = now();
            } elseif ($data['stage'] === 'lost' && !$deal->lost_at) {
                $data['lost_at'] = now();
            }
        }

        $deal->update($data);
        return $deal->fresh(['client', 'assignedUser']);
    }

    /**
     * Move deal to a different stage.
     */
    public function moveDealToStage(Deal $deal, string $stage): Deal
    {
        $deal->moveToStage($stage);
        return $deal->fresh(['client', 'assignedUser']);
    }

    /**
     * Delete deal.
     */
    public function deleteDeal(Deal $deal): bool
    {
        return $deal->delete();
    }

    /**
     * Mark deal as won.
     */
    public function markAsWon(Deal $deal): Deal
    {
        $deal->markAsWon();
        return $deal->fresh();
    }

    /**
     * Mark deal as lost.
     */
    public function markAsLost(Deal $deal, string $reason = null): Deal
    {
        $deal->markAsLost($reason);
        return $deal->fresh();
    }

    /**
     * Get deal statistics.
     */
    public function getStatistics(User $user): array
    {
        $query = Deal::query();

        // Filter by permissions
        if ($user->role === 'user' && $user->is_metatech_employee) {
            $query->where('assigned_to', $user->id);
        }

        $totalDeals = $query->count();
        $activeDeals = (clone $query)->active()->count();
        $wonDeals = (clone $query)->won()->count();
        $lostDeals = (clone $query)->lost()->count();
        $totalValue = (clone $query)->sum('value');
        $wonValue = (clone $query)->won()->sum('value');
        $pipelineValue = (clone $query)->active()->sum('value');

        // Win rate
        $closedDeals = $wonDeals + $lostDeals;
        $winRate = $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100, 2) : 0;

        return [
            'total_deals' => $totalDeals,
            'active_deals' => $activeDeals,
            'won_deals' => $wonDeals,
            'lost_deals' => $lostDeals,
            'total_value' => $totalValue,
            'won_value' => $wonValue,
            'pipeline_value' => $pipelineValue,
            'win_rate' => $winRate,
        ];
    }

    /**
     * Get deals by stage counts.
     */
    public function getStageStatistics(User $user): array
    {
        $query = Deal::query();

        // Filter by permissions
        if ($user->role === 'user' && $user->is_metatech_employee) {
            $query->where('assigned_to', $user->id);
        }

        $stageCounts = $query->selectRaw('stage, COUNT(*) as count, SUM(value) as total_value')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        $stages = ['new_lead', 'contacted', 'qualified', 'proposal_sent', 'negotiation', 'won', 'lost'];
        $result = [];

        foreach ($stages as $stage) {
            $result[$stage] = [
                'count' => $stageCounts->get($stage)->count ?? 0,
                'total_value' => $stageCounts->get($stage)->total_value ?? 0,
            ];
        }

        return $result;
    }
}

