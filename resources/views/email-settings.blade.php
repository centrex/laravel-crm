<x-layouts::app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">CRM Email Settings</h2>
                <p class="mt-1 text-sm text-gray-500">Sender identity, reply routing, and default CRM email subjects.</p>
            </div>
            <a href="{{ route('crm.dashboard') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-indigo-200 hover:text-indigo-700">
                Back to CRM
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 sm:px-6 lg:grid-cols-[1.35fr_0.65fr] lg:px-8">
            <form method="POST" action="{{ route('crm.email-settings.update') }}" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                @csrf

                @if (session('crm_success'))
                    <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('crm_success') }}
                    </div>
                @endif

                <div class="mb-6">
                    <p class="text-sm font-medium text-gray-500">Email defaults</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">CRM notification identity</h3>
                </div>

                <input type="hidden" name="enabled" value="0" />
                <label class="mb-5 flex items-start gap-3 rounded-xl border border-gray-100 p-4">
                    <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $emailSettings['enabled'])) class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <span>
                        <span class="block text-sm font-medium text-gray-800">Enable CRM email notifications</span>
                        <span class="mt-1 block text-sm leading-6 text-gray-500">Use these defaults when CRM workflows send assignment, deal, or activity messages.</span>
                    </span>
                </label>

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">From address</span>
                        <input type="email" name="from_address" value="{{ old('from_address', $emailSettings['from_address']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-indigo-400" />
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">From name</span>
                        <input type="text" name="from_name" value="{{ old('from_name', $emailSettings['from_name']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-indigo-400" />
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Reply-to address</span>
                        <input type="email" name="reply_to" value="{{ old('reply_to', $emailSettings['reply_to']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-indigo-400" />
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Default owner email</span>
                        <input type="email" name="default_owner_email" value="{{ old('default_owner_email', $emailSettings['default_owner_email']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-indigo-400" />
                    </label>
                </div>

                <div class="mt-6 grid gap-4">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Lead email subject</span>
                        <input type="text" name="lead_subject" value="{{ old('lead_subject', $emailSettings['lead_subject']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-indigo-400" />
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Deal email subject</span>
                        <input type="text" name="deal_subject" value="{{ old('deal_subject', $emailSettings['deal_subject']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-indigo-400" />
                    </label>

                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-gray-700">Activity email subject</span>
                        <input type="text" name="activity_subject" value="{{ old('activity_subject', $emailSettings['activity_subject']) }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-indigo-400" />
                    </label>
                </div>

                <button type="submit" class="mt-6 rounded-lg bg-indigo-600 px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                    Save CRM email settings
                </button>
            </form>

            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-gray-500">Current CRM mail profile</p>
                <h3 class="mt-1 text-lg font-semibold text-gray-900">{{ $emailSettings['from_name'] }}</h3>
                <div class="mt-5 space-y-4 text-sm leading-6 text-gray-500">
                    <div class="rounded-xl border border-gray-100 p-4">
                        Status: {{ $emailSettings['enabled'] ? 'Enabled' : 'Disabled' }}<br>
                        From: {{ $emailSettings['from_address'] }}<br>
                        Reply-to: {{ $emailSettings['reply_to'] }}
                    </div>
                    <div class="rounded-xl border border-gray-100 p-4">
                        Lead: {{ $emailSettings['lead_subject'] }}<br>
                        Deal: {{ $emailSettings['deal_subject'] }}<br>
                        Activity: {{ $emailSettings['activity_subject'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
