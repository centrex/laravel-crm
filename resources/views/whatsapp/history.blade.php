<x-layouts::app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">WhatsApp Message History</h2>
                <p class="mt-1 text-sm text-gray-500">All generated WhatsApp links — click Open to launch WhatsApp Web.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('crm.whatsapp.compose') }}" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-green-700">
                    + New Message
                </a>
                <a href="{{ route('crm.dashboard') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-indigo-200 hover:text-indigo-700">
                    ← CRM
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('crm_success'))
                @php $ids = session('crm_wa_message_ids', []); @endphp
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4">
                    <p class="text-sm font-medium text-emerald-800">{{ session('crm_success') }}</p>

                    @if (!empty($ids))
                        @php
                            $fresh = \Centrex\Crm\Models\WhatsappMessage::query()
                                ->with('contact')
                                ->whereIn('id', $ids)
                                ->orderBy('id')
                                ->get();
                        @endphp
                        <div class="mt-3 space-y-2">
                            @foreach ($fresh as $msg)
                                <div class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-white px-4 py-3">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-800">{{ $msg->contact?->full_name ?? $msg->phone }}</p>
                                        <p class="text-xs text-gray-400">{{ $msg->phone }}</p>
                                    </div>
                                    <a href="{{ route('crm.whatsapp.open', $msg) }}"
                                       target="_blank"
                                       class="flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-green-700">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.125.558 4.126 1.534 5.87L0 24l6.305-1.517A11.955 11.955 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 0 1-5.032-1.383l-.36-.214-3.742.9.934-3.649-.234-.374A9.818 9.818 0 1 1 12 21.818z"/>
                                        </svg>
                                        Open in WhatsApp
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-500">Contact</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-500">Phone</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-500">Type</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-500">Message preview</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-500">Status</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-500">Sent</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($messages as $msg)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-800">
                                    {{ $msg->contact?->full_name ?? '—' }}
                                    @if ($msg->contact?->company)
                                        <span class="block text-xs font-normal text-gray-400">{{ $msg->contact->company->name }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 font-mono text-gray-600">{{ $msg->phone }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                        {{ $msg->type === \Centrex\Crm\Enums\WhatsappMessageType::Offer ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $msg->type === \Centrex\Crm\Enums\WhatsappMessageType::ProductUpdate ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $msg->type === \Centrex\Crm\Enums\WhatsappMessageType::FollowUp ? 'bg-purple-100 text-purple-700' : '' }}
                                        {{ $msg->type === \Centrex\Crm\Enums\WhatsappMessageType::Custom ? 'bg-gray-100 text-gray-600' : '' }}
                                    ">{{ $msg->type->label() }}</span>
                                </td>
                                <td class="max-w-xs px-5 py-3 text-gray-500">
                                    <p class="truncate text-xs">{{ $msg->message_body }}</p>
                                </td>
                                <td class="px-5 py-3">
                                    @if ($msg->status === 'opened')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Opened
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400">{{ $msg->created_at->diffForHumans() }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ route('crm.whatsapp.open', $msg) }}"
                                       target="_blank"
                                       class="flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-green-700 whitespace-nowrap">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.125.558 4.126 1.534 5.87L0 24l6.305-1.517A11.955 11.955 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 0 1-5.032-1.383l-.36-.214-3.742.9.934-3.649-.234-.374A9.818 9.818 0 1 1 12 21.818z"/>
                                        </svg>
                                        Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-16 text-center text-sm text-gray-400">
                                    No WhatsApp messages yet.
                                    <a href="{{ route('crm.whatsapp.compose') }}" class="ml-1 font-medium text-green-600 hover:underline">Send your first message →</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($messages->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">
                        {{ $messages->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
