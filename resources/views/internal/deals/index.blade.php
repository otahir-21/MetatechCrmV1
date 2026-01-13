<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sales Pipeline - Metatech CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Sales Pipeline</h1>
                        <p class="text-sm text-gray-600">Manage your deals and track progress</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="{{ route('internal.deals.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            + New Deal
                        </a>
                        <a href="{{ route('internal.clients.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            Clients
                        </a>
                        <a href="{{ route('internal.dashboard') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            ← Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Statistics -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-600">Active Deals</p>
                    <p class="text-2xl font-bold text-gray-900" id="active-deals-count">{{ $statistics['active_deals'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-600">Pipeline Value</p>
                    <p class="text-2xl font-bold text-indigo-600" id="pipeline-value">${{ number_format($statistics['pipeline_value'], 2) }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-600">Won Deals</p>
                    <p class="text-2xl font-bold text-green-600" id="won-deals-count">{{ $statistics['won_deals'] }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <p class="text-sm text-gray-600">Win Rate</p>
                    <p class="text-2xl font-bold text-blue-600" id="win-rate">{{ $statistics['win_rate'] }}%</p>
                </div>
            </div>

            <!-- Kanban Board -->
            <div class="flex space-x-4 overflow-x-auto pb-4">
                @foreach(['new_lead' => 'New Lead', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'proposal_sent' => 'Proposal Sent', 'negotiation' => 'Negotiation', 'won' => 'Won', 'lost' => 'Lost'] as $stage => $label)
                <div class="flex-shrink-0 w-80">
                    <div class="bg-gray-200 rounded-lg p-3 mb-3">
                        <h3 class="font-semibold text-gray-700">{{ $label }}</h3>
                        <span class="text-xs text-gray-600">(<span class="stage-count" data-stage="{{ $stage }}">{{ count($dealsByStage[$stage]) }}</span> deals)</span>
                    </div>
                    <div class="space-y-3 min-h-[200px] deals-column" data-stage="{{ $stage }}">
                        @foreach($dealsByStage[$stage] as $deal)
                        <div class="bg-white rounded-lg shadow p-4 cursor-move deal-card" data-deal-id="{{ $deal->id }}" data-deal-value="{{ $deal->value }}" draggable="true">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-gray-900 text-sm">{{ $deal->title }}</h4>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($deal->priority === 'high') bg-red-100 text-red-700
                                    @elseif($deal->priority === 'medium') bg-yellow-100 text-yellow-700
                                    @else bg-green-100 text-green-700
                                    @endif">
                                    {{ ucfirst($deal->priority) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mb-2">{{ $deal->client->name }}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-indigo-600">${{ number_format($deal->value, 0) }}</span>
                                <span class="text-xs text-gray-500">{{ $deal->assignedUser->name }}</span>
                            </div>
                            <a href="{{ route('internal.deals.show', $deal) }}" class="text-xs text-indigo-600 hover:text-indigo-800 mt-2 block">View →</a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        // Drag and Drop functionality
        document.addEventListener('DOMContentLoaded', function() {
            const columns = document.querySelectorAll('.deals-column');
            
            // Track statistics for calculations
            let wonDealsCount = {{ $statistics['won_deals'] }};
            let lostDealsCount = {{ $statistics['lost_deals'] }};
            
            // Function to update stage count
            function updateStageCount(stage, delta) {
                const countElement = document.querySelector(`.stage-count[data-stage="${stage}"]`);
                if (countElement) {
                    const currentCount = parseInt(countElement.textContent);
                    countElement.textContent = currentCount + delta;
                }
            }
            
            // Function to update statistics
            function updateStatistics(oldStage, newStage, dealValue) {
                const activeDealsEl = document.getElementById('active-deals-count');
                const wonDealsEl = document.getElementById('won-deals-count');
                const pipelineValueEl = document.getElementById('pipeline-value');
                const winRateEl = document.getElementById('win-rate');
                
                const isOldStageActive = !['won', 'lost'].includes(oldStage);
                const isNewStageActive = !['won', 'lost'].includes(newStage);
                
                // Update Active Deals
                if (isOldStageActive && !isNewStageActive) {
                    // Moving from active to won/lost
                    const currentActive = parseInt(activeDealsEl.textContent);
                    activeDealsEl.textContent = currentActive - 1;
                } else if (!isOldStageActive && isNewStageActive) {
                    // Moving from won/lost to active
                    const currentActive = parseInt(activeDealsEl.textContent);
                    activeDealsEl.textContent = currentActive + 1;
                }
                
                // Update Won Deals
                if (newStage === 'won' && oldStage !== 'won') {
                    wonDealsCount++;
                    wonDealsEl.textContent = wonDealsCount;
                } else if (oldStage === 'won' && newStage !== 'won') {
                    wonDealsCount--;
                    wonDealsEl.textContent = wonDealsCount;
                }
                
                // Update Lost Deals count (for win rate calculation)
                if (newStage === 'lost' && oldStage !== 'lost') {
                    lostDealsCount++;
                } else if (oldStage === 'lost' && newStage !== 'lost') {
                    lostDealsCount--;
                }
                
                // Update Win Rate
                const totalClosed = wonDealsCount + lostDealsCount;
                if (totalClosed > 0) {
                    const winRate = Math.round((wonDealsCount / totalClosed) * 100 * 100) / 100;
                    winRateEl.textContent = winRate + '%';
                } else {
                    winRateEl.textContent = '0%';
                }
                
                // Update Pipeline Value
                if (pipelineValueEl) {
                    const currentPipelineText = pipelineValueEl.textContent.replace(/[$,]/g, '');
                    let currentPipeline = parseFloat(currentPipelineText);
                    
                    if (isOldStageActive && !isNewStageActive) {
                        // Moving from active to won/lost - subtract from pipeline
                        currentPipeline -= parseFloat(dealValue);
                    } else if (!isOldStageActive && isNewStageActive) {
                        // Moving from won/lost to active - add to pipeline
                        currentPipeline += parseFloat(dealValue);
                    }
                    
                    pipelineValueEl.textContent = '$' + currentPipeline.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                }
            }
            
            columns.forEach(column => {
                new Sortable(column, {
                    group: 'deals',
                    animation: 150,
                    ghostClass: 'bg-indigo-100',
                    onEnd: function(evt) {
                        const dealId = evt.item.dataset.dealId;
                        const dealValue = evt.item.dataset.dealValue;
                        const oldStage = evt.from.dataset.stage;
                        const newStage = evt.to.dataset.stage;
                        
                        // If the deal wasn't actually moved to a different stage, do nothing
                        if (oldStage === newStage) {
                            return;
                        }
                        
                        // Update deal stage via AJAX
                        fetch(`/internal/deals/${dealId}/stage`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ stage: newStage })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Deal moved successfully!');
                                
                                // Update the stage counts
                                updateStageCount(oldStage, -1);
                                updateStageCount(newStage, 1);
                                
                                // Update all statistics
                                updateStatistics(oldStage, newStage, dealValue);
                            } else {
                                alert('Failed to move deal. Please refresh the page.');
                                location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred. Please refresh the page.');
                            location.reload();
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>

