@php
    $cards = [
        ['label' => 'Open Leads', 'value' => $summary['open_leads'] ?? 0],
        ['label' => 'Active Deals', 'value' => $summary['active_deals'] ?? 0],
        ['label' => 'Pipeline Value', 'value' => number_format((float) ($summary['pipeline_value'] ?? 0), 2)],
        ['label' => 'Weighted Pipeline', 'value' => number_format((float) ($summary['weighted_pipeline_value'] ?? 0), 2)],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                CRM Workspace
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Lead qualification, deal pipeline, and follow-up visibility in one place.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($cards as $card)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="text-sm text-gray-500">{{ $card['label'] }}</div>
                        <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $card['value'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Pipeline by Stage</h3>
                        <span class="text-sm text-gray-500">{{ count($dealsByStage) }} stages tracked</span>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="py-3 pr-4 font-medium">Stage</th>
                                    <th class="py-3 pr-4 font-medium">Deals</th>
                                    <th class="py-3 font-medium">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($dealsByStage as $row)
                                    <tr>
                                        <td class="py-3 pr-4 font-medium text-gray-900">{{ ucfirst($row['stage']) }}</td>
                                        <td class="py-3 pr-4 text-gray-600">{{ $row['count'] }}</td>
                                        <td class="py-3 text-gray-600">{{ number_format((float) $row['amount'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-500">No deals in the pipeline yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Upcoming Activities</h3>
                        <span class="text-sm text-gray-500">{{ count($upcomingActivities) }} scheduled</span>
                    </div>

                    <div class="mt-4 space-y-4">
                        @forelse ($upcomingActivities as $activity)
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $activity->summary }}</div>
                                        <div class="mt-1 text-sm text-gray-500">{{ ucfirst($activity->type->value) }}</div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ optional($activity->due_at)->format('M j, Y g:i A') ?? 'No due date' }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No upcoming follow-ups. Log an activity against a lead or deal to start tracking the queue.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
