<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClientService
{
    /**
     * Get all clients (filtered by user permissions).
     */
    public function getAllClients(User $user)
    {
        $query = Client::with('creator', 'deals');

        // If user is sales agent (role = user), show only their clients
        if ($user->role === 'user' && $user->is_metatech_employee) {
            $query->where('created_by', $user->id);
        }

        return $query->latest()->get();
    }

    /**
     * Create a new client.
     */
    public function createClient(array $data, User $user): Client
    {
        return DB::transaction(function () use ($data, $user) {
            $data['created_by'] = $user->id;
            $data['status'] = $data['status'] ?? 'prospect';

            return Client::create($data);
        });
    }

    /**
     * Update existing client.
     */
    public function updateClient(Client $client, array $data): Client
    {
        $client->update($data);
        return $client->fresh();
    }

    /**
     * Delete client.
     */
    public function deleteClient(Client $client): bool
    {
        return $client->delete();
    }

    /**
     * Get client with all deals.
     */
    public function getClientWithDeals(int $clientId): Client
    {
        return Client::with(['deals.assignedUser', 'creator'])->findOrFail($clientId);
    }

    /**
     * Search clients.
     */
    public function searchClients(string $query, User $user)
    {
        $searchQuery = Client::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('contact_person', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%");
        });

        // Filter by permissions
        if ($user->role === 'user' && $user->is_metatech_employee) {
            $searchQuery->where('created_by', $user->id);
        }

        return $searchQuery->latest()->get();
    }
}

