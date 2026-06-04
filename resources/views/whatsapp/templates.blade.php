<x-layouts::app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">WhatsApp Templates</h2>
                <p class="mt-1 text-sm text-gray-500">Reusable message templates with variable placeholders.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('crm.whatsapp.compose') }}" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-green-700">
                    ↗ Compose
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
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('crm_success') }}
                </div>
            @endif

            <div class="grid gap-8 lg:grid-cols-[1.4fr_0.6fr]">

                {{-- Template list --}}
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-500">Name</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-500">Type</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-500">Preview</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-500">Status</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($templates as $tpl)
                                <tr class="hover:bg-gray-50 {{ $tpl->is_active ? '' : 'opacity-50' }}">
                                    <td class="px-5 py-3 font-medium text-gray-800">{{ $tpl->name }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                            {{ $tpl->type === \Centrex\Crm\Enums\WhatsappMessageType::Offer ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $tpl->type === \Centrex\Crm\Enums\WhatsappMessageType::ProductUpdate ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $tpl->type === \Centrex\Crm\Enums\WhatsappMessageType::FollowUp ? 'bg-purple-100 text-purple-700' : '' }}
                                            {{ $tpl->type === \Centrex\Crm\Enums\WhatsappMessageType::Custom ? 'bg-gray-100 text-gray-600' : '' }}
                                        ">{{ $tpl->type->label() }}</span>
                                    </td>
                                    <td class="max-w-xs px-5 py-3 text-xs text-gray-400">
                                        <p class="truncate">{{ $tpl->message_body }}</p>
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($tpl->is_active)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Active</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <form method="POST" action="{{ route('crm.whatsapp.templates.toggle', $tpl) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xs font-medium text-gray-500 hover:text-gray-800">
                                                {{ $tpl->is_active ? 'Disable' : 'Enable' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-sm text-gray-400">No templates yet — create one →</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if ($templates->hasPages())
                        <div class="border-t border-gray-100 px-5 py-4">
                            {{ $templates->links() }}
                        </div>
                    @endif
                </div>

                {{-- Create template form --}}
                <div>
                    <form method="POST" action="{{ route('crm.whatsapp.templates.store') }}"
                          class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        @csrf

                        <p class="mb-5 text-sm font-semibold text-gray-800">New template</p>

                        <div class="space-y-4">
                            <label class="block">
                                <span class="mb-1.5 block text-sm font-medium text-gray-700">Template name</span>
                                <input type="text" name="name" value="{{ old('name') }}"
                                       class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-green-400"
                                       placeholder="e.g. Monthly Offer" />
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </label>

                            <label class="block">
                                <span class="mb-1.5 block text-sm font-medium text-gray-700">Type</span>
                                <select name="type" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-green-400">
                                    @foreach ($types as $t)
                                        <option value="{{ $t->value }}" @selected(old('type') === $t->value)>{{ $t->label() }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </label>

                            <label class="block">
                                <span class="mb-1.5 block text-sm font-medium text-gray-700">
                                    Message body
                                    <span class="ml-1 font-normal text-gray-400 text-xs">@{{name}}, @{{company}}, @{{product}}, @{{offer}}, @{{price}}, @{{message}}, @{{sender}}</span>
                                </span>
                                <textarea name="message_body" rows="10"
                                          class="w-full rounded-xl border border-gray-200 px-4 py-3 font-mono text-sm outline-none transition focus:border-green-400"
                                          placeholder="Hi @{{name}},&#10;&#10;We have a great offer for you...">{{ old('message_body') }}</textarea>
                                @error('message_body')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </label>
                        </div>

                        <button type="submit" class="mt-5 w-full rounded-xl bg-green-600 px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-green-700">
                            Save template
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
