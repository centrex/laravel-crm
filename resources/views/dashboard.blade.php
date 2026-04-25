@php
    $currency = config('crm.currency', 'BDT');

    $kpis = [
        [
            'label'   => 'Open Leads',
            'value'   => $summary['open_leads'] ?? 0,
            'sub'     => ($summary['qualified_leads'] ?? 0) . ' qualified',
            'color'   => 'indigo',
            'icon'    => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
        ],
        [
            'label'   => 'Active Deals',
            'value'   => $summary['active_deals'] ?? 0,
            'sub'     => number_format((float) ($summary['pipeline_value'] ?? 0), 0) . ' pipeline',
            'color'   => 'blue',
            'icon'    => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
        ],
        [
            'label'   => 'Won Deals',
            'value'   => $summary['won_deals'] ?? 0,
            'sub'     => number_format($conversionRates['deal_win_rate'] ?? 0, 1) . '% win rate',
            'color'   => 'emerald',
            'icon'    => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        ],
        [
            'label'   => 'Weighted Pipeline',
            'value'   => number_format((float) ($summary['weighted_pipeline_value'] ?? 0), 0),
            'sub'     => 'probability-adjusted',
            'color'   => 'violet',
            'icon'    => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        ],
    ];

    $stageColors = [
        'qualified'   => ['bg' => 'bg-blue-500',   'badge' => 'bg-blue-100 text-blue-700'],
        'proposal'    => ['bg' => 'bg-yellow-500',  'badge' => 'bg-yellow-100 text-yellow-700'],
        'negotiation' => ['bg' => 'bg-orange-500',  'badge' => 'bg-orange-100 text-orange-700'],
        'won'         => ['bg' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-700'],
        'lost'        => ['bg' => 'bg-red-400',     'badge' => 'bg-red-100 text-red-600'],
    ];

    $priorityColors = [
        'urgent' => 'border-l-red-500 bg-red-50',
        'high'   => 'border-l-orange-400 bg-orange-50',
        'normal' => 'border-l-blue-400 bg-white',
        'low'    => 'border-l-gray-300 bg-gray-50',
    ];

    $priorityDot = [
        'urgent' => 'bg-red-500',
        'high'   => 'bg-orange-400',
        'normal' => 'bg-blue-400',
        'low'    => 'bg-gray-400',
    ];

    $typeIcons = [
        'call'    => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z',
        'email'   => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75',
        'meeting' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
        'task'    => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'note'    => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
    ];

    $maxDealAmount = collect($dealsByStage)->max('amount') ?: 1;
    $overdueCount = count($overdueActivities);

    $forecastTotal = collect($forecast)->sum('weighted_revenue');
@endphp

<x-layouts::app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">CRM Dashboard</h2>
                <p class="mt-1 text-sm text-gray-500">Pipeline overview, activities, CLV insights, and revenue forecast.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('crm.email-settings.edit') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-indigo-200 hover:text-indigo-700">
                    Email settings
                </a>
            @if ($overdueCount > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-700">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    {{ $overdueCount }} overdue {{ Str::plural('activity', $overdueCount) }}
                </span>
            @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- KPI Cards --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($kpis as $kpi)
                    @php
                        $colorMap = [
                            'indigo'  => ['ring' => 'ring-indigo-100',  'icon' => 'bg-indigo-50 text-indigo-600',  'num' => 'text-indigo-700'],
                            'blue'    => ['ring' => 'ring-blue-100',    'icon' => 'bg-blue-50 text-blue-600',      'num' => 'text-blue-700'],
                            'emerald' => ['ring' => 'ring-emerald-100', 'icon' => 'bg-emerald-50 text-emerald-600','num' => 'text-emerald-700'],
                            'violet'  => ['ring' => 'ring-violet-100',  'icon' => 'bg-violet-50 text-violet-600',  'num' => 'text-violet-700'],
                        ];
                        $c = $colorMap[$kpi['color']];
                    @endphp
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm ring-1 {{ $c['ring'] }}">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">{{ $kpi['label'] }}</p>
                                <p class="mt-1 text-2xl font-bold {{ $c['num'] }}">{{ $kpi['value'] }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $kpi['sub'] }}</p>
                            </div>
                            <div class="rounded-xl p-2.5 {{ $c['icon'] }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}" />
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Conversion Rate Bar --}}
            <div class="rounded-2xl border border-gray-100 bg-white px-6 py-4 shadow-sm">
                <div class="flex flex-wrap items-center gap-6">
                    <span class="text-sm font-medium text-gray-600">Funnel:</span>
                    @php
                        $funnel = [
                            ['label' => 'Total Leads',    'val' => $conversionRates['total_leads']],
                            ['label' => 'Qualified',      'val' => $conversionRates['qualified_leads']],
                            ['label' => 'Total Deals',    'val' => $conversionRates['total_deals']],
                            ['label' => 'Won',            'val' => $conversionRates['won_deals']],
                        ];
                    @endphp
                    @foreach ($funnel as $i => $f)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-semibold text-gray-800">{{ $f['val'] }}</span>
                            <span class="text-gray-400">{{ $f['label'] }}</span>
                        </div>
                        @if (!$loop->last)
                            <span class="text-gray-300">→</span>
                        @endif
                    @endforeach
                    <div class="ml-auto flex gap-6 text-sm">
                        <div>
                            <span class="font-semibold text-indigo-700">{{ $conversionRates['lead_to_qualified'] }}%</span>
                            <span class="ml-1 text-gray-400">lead→qualified</span>
                        </div>
                        <div>
                            <span class="font-semibold text-emerald-700">{{ $conversionRates['deal_win_rate'] }}%</span>
                            <span class="ml-1 text-gray-400">win rate</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pipeline + Upcoming Activities --}}
            <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">

                {{-- Pipeline by Stage --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Pipeline by Stage</h3>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                            {{ $summary['active_deals'] ?? 0 }} active
                        </span>
                    </div>

                    @if (count($dealsByStage) === 0)
                        <p class="py-8 text-center text-sm text-gray-400">No deals in the pipeline yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($dealsByStage as $row)
                                @php
                                    $sc = $stageColors[$row['stage']] ?? ['bg' => 'bg-gray-400', 'badge' => 'bg-gray-100 text-gray-600'];
                                    $pct = $maxDealAmount > 0 ? round($row['amount'] / $maxDealAmount * 100) : 0;
                                @endphp
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $sc['badge'] }}">
                                                {{ ucfirst($row['stage']) }}
                                            </span>
                                            <span class="text-gray-500">{{ $row['count'] }} {{ Str::plural('deal', $row['count']) }}</span>
                                        </div>
                                        <span class="font-medium text-gray-700">{{ number_format((float) $row['amount'], 0) }}</span>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full {{ $sc['bg'] }} transition-all duration-500" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 border-t border-gray-100 pt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Pipeline value</span>
                                <span class="font-semibold text-gray-800">{{ number_format((float) ($summary['pipeline_value'] ?? 0), 0) }}</span>
                            </div>
                            <div class="mt-1 flex justify-between text-sm">
                                <span class="text-gray-500">Weighted (probability-adjusted)</span>
                                <span class="font-semibold text-violet-700">{{ number_format((float) ($summary['weighted_pipeline_value'] ?? 0), 0) }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Upcoming Activities --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Upcoming Activities</h3>
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                            {{ count($upcomingActivities) }} scheduled
                        </span>
                    </div>

                    @if ($upcomingActivities->isEmpty())
                        <p class="py-8 text-center text-sm text-gray-400">No upcoming activities. Log one against a lead or deal.</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($upcomingActivities as $activity)
                                @php
                                    $priority = $activity->priority?->value ?? 'normal';
                                    $type = $activity->type?->value ?? 'note';
                                    $pc = $priorityColors[$priority] ?? 'border-l-gray-300 bg-gray-50';
                                    $dot = $priorityDot[$priority] ?? 'bg-gray-400';
                                    $icon = $typeIcons[$type] ?? $typeIcons['note'];
                                    $isToday = optional($activity->due_at)->isToday();
                                    $isTomorrow = optional($activity->due_at)->isTomorrow();
                                    $dueLabel = match(true) {
                                        $isToday    => 'Today',
                                        $isTomorrow => 'Tomorrow',
                                        default     => optional($activity->due_at)?->format('M j') ?? '—',
                                    };
                                @endphp
                                <div class="flex items-start gap-3 rounded-xl border-l-4 p-3 {{ $pc }}">
                                    <div class="mt-0.5 shrink-0 rounded-lg bg-white p-1.5 shadow-sm">
                                        <svg class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-800">{{ $activity->summary }}</p>
                                        <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-400">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
                                            <span>{{ ucfirst($type) }}</span>
                                            <span>·</span>
                                            <span class="{{ $isToday ? 'font-semibold text-orange-600' : '' }}">{{ $dueLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Overdue Activities (only if any) --}}
            @if ($overdueCount > 0)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-2">
                        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <h3 class="text-base font-semibold text-red-800">Overdue Activities ({{ $overdueCount }})</h3>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($overdueActivities->take(6) as $activity)
                            @php
                                $type = $activity->type?->value ?? 'note';
                                $daysOverdue = optional($activity->due_at)?->diffInDays(now());
                            @endphp
                            <div class="flex items-start gap-3 rounded-xl bg-white p-3 shadow-sm">
                                <div class="shrink-0">
                                    <svg class="h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $typeIcons[$type] ?? $typeIcons['note'] }}" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-800">{{ $activity->summary }}</p>
                                    <p class="mt-0.5 text-xs text-red-600">
                                        {{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }} overdue · {{ ucfirst($type) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if ($overdueCount > 6)
                        <p class="mt-3 text-sm text-red-600">+{{ $overdueCount - 6 }} more overdue activities.</p>
                    @endif
                </div>
            @endif

            {{-- Revenue Forecast + CLV Leaderboard --}}
            <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">

                {{-- 3-Month Forecast --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">3-Month Revenue Forecast</h3>
                            <p class="mt-0.5 text-xs text-gray-400">Based on deals with expected close dates</p>
                        </div>
                        @if ($forecastTotal > 0)
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                {{ number_format($forecastTotal, 0) }} total
                            </span>
                        @endif
                    </div>

                    @if (count($forecast) === 0 || $forecastTotal == 0)
                        <p class="py-8 text-center text-sm text-gray-400">No deals with close dates in the next 3 months.</p>
                    @else
                        @php
                            $maxForecast = collect($forecast)->max('weighted_revenue') ?: 1;
                        @endphp
                        <div class="space-y-4">
                            @foreach ($forecast as $month)
                                @php
                                    $pct = $maxForecast > 0 ? round($month['weighted_revenue'] / $maxForecast * 100) : 0;
                                @endphp
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-700">{{ \Carbon\Carbon::createFromFormat('Y-m', $month['month'])->format('M Y') }}</span>
                                            <span class="text-gray-400">{{ $month['deal_count'] }} {{ Str::plural('deal', $month['deal_count']) }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-semibold text-emerald-700">{{ number_format($month['weighted_revenue'], 0) }}</span>
                                            <span class="ml-1 text-xs text-gray-400">weighted</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-2 rounded-full bg-emerald-400 transition-all duration-500" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="w-24 text-right text-xs text-gray-400">{{ number_format($month['expected_revenue'], 0) }} gross</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- CLV Leaderboard --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Top Customers by CLV</h3>
                            <p class="mt-0.5 text-xs text-gray-400">Customer Lifetime Value — {{ config('crm.clv_horizon_months', 12) }}-month horizon</p>
                        </div>
                        <svg class="h-5 w-5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                        </svg>
                    </div>

                    @if ($clvLeaderboard->isEmpty())
                        <div class="py-8 text-center">
                            <p class="text-sm text-gray-400">No CLV data yet.</p>
                            <p class="mt-1 text-xs text-gray-400">Run <code class="rounded bg-gray-100 px-1 py-0.5 font-mono text-gray-600">php artisan crm:calculate-clv</code> to generate.</p>
                        </div>
                    @else
                        @php $maxClv = $clvLeaderboard->max(fn ($s) => (float) $s->clv_value) ?: 1; @endphp
                        <div class="space-y-3">
                            @foreach ($clvLeaderboard as $i => $snapshot)
                                @php
                                    $clv = (float) $snapshot->clv_value;
                                    $pct = round($clv / $maxClv * 100);
                                    $medalColors = ['text-yellow-500', 'text-gray-400', 'text-amber-600'];
                                @endphp
                                <div class="flex items-center gap-3">
                                    <span class="w-5 shrink-0 text-center text-sm font-bold {{ $medalColors[$i] ?? 'text-gray-400' }}">
                                        {{ $i + 1 }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="truncate font-medium text-gray-800">
                                                {{ $snapshot->contact?->full_name ?? 'Unknown' }}
                                            </span>
                                            <span class="ml-2 shrink-0 font-semibold text-violet-700">
                                                {{ number_format($clv, 0) }}
                                            </span>
                                        </div>
                                        <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-1.5 rounded-full bg-violet-400 transition-all duration-500" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <div class="mt-0.5 flex items-center gap-2 text-xs text-gray-400">
                                            <span>{{ $snapshot->frequency }} repeat {{ Str::plural('purchase', $snapshot->frequency) }}</span>
                                            <span>·</span>
                                            <span>{{ round((float) $snapshot->p_alive * 100) }}% alive</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-layouts::app>
