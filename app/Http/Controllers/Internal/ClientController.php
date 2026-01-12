<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Display list of clients.
     */
    public function index()
    {
        $clients = $this->clientService->getAllClients(auth()->user());
        
        return view('internal.clients.index', compact('clients'));
    }

    /**
     * Show form to create new client.
     */
    public function create()
    {
        return view('internal.clients.create');
    }

    /**
     * Store new client.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,prospect',
        ]);

        $client = $this->clientService->createClient($validated, auth()->user());

        return redirect()->route('internal.clients.index')
            ->with('success', 'Client created successfully!');
    }

    /**
     * Show specific client with deals.
     */
    public function show(Client $client)
    {
        $client = $this->clientService->getClientWithDeals($client->id);
        
        return view('internal.clients.show', compact('client'));
    }

    /**
     * Show form to edit client.
     */
    public function edit(Client $client)
    {
        return view('internal.clients.edit', compact('client'));
    }

    /**
     * Update client.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,prospect',
        ]);

        $client = $this->clientService->updateClient($client, $validated);

        return redirect()->route('internal.clients.show', $client)
            ->with('success', 'Client updated successfully!');
    }

    /**
     * Delete client.
     */
    public function destroy(Client $client)
    {
        $this->clientService->deleteClient($client);

        return redirect()->route('internal.clients.index')
            ->with('success', 'Client deleted successfully!');
    }
}
