<x-layouts::app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Send WhatsApp Message</h2>
                <p class="mt-1 text-sm text-gray-500">Compose a product update or offer and generate WhatsApp Web links for your contacts.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('crm.whatsapp.templates') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-green-200 hover:text-green-700">
                    Templates
                </a>
                <a href="{{ route('crm.whatsapp.history') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-green-200 hover:text-green-700">
                    History
                </a>
                <a href="{{ route('crm.dashboard') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-indigo-200 hover:text-indigo-700">
                    ← CRM
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="whatsappCompose()" x-init="init()">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('crm_success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('crm_success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('crm.whatsapp.send') }}" class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
                @csrf

                {{-- Left: compose panel --}}
                <div class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

                    {{-- Message type --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Message type</label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ($types as $t)
                                <label class="flex cursor-pointer items-center gap-2 rounded-xl border p-3 transition"
                                       :class="type === '{{ $t->value }}' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-200'">
                                    <input type="radio" name="type" value="{{ $t->value }}" x-model="type" class="sr-only" />
                                    <span class="text-xs font-medium text-gray-700">{{ $t->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Template selector --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Template (optional)</label>
                        <select name="template_id" x-model="templateId" @change="loadTemplate()"
                                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-green-400">
                            <option value="">— No template, write custom message —</option>
                            @foreach ($templates as $tpl)
                                <option value="{{ $tpl->id }}" data-body="{{ e($tpl->message_body) }}" data-type="{{ $tpl->type->value }}">
                                    {{ $tpl->name }} ({{ $tpl->type->label() }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Message body --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Message body
                            <span class="ml-1 font-normal text-gray-400 text-xs">— use @{{name}}, @{{company}}, @{{product}}, @{{offer}}, @{{price}}, @{{message}}, @{{sender}}</span>
                        </label>
                        <textarea name="message_body" x-model="messageBody" rows="10"
                                  class="w-full rounded-xl border border-gray-200 px-4 py-3 font-mono text-sm outline-none transition focus:border-green-400"
                                  placeholder="Hi @{{name}}, we have a special offer..."></textarea>
                        @error('message_body')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400" x-text="messageBody.length + ' / 4096 chars'"></p>
                    </div>

                    {{-- Preview --}}
                    <div x-show="selectedContacts.length > 0 && messageBody.length > 0">
                        <p class="mb-2 text-sm font-medium text-gray-700">Preview (first selected contact)</p>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 px-4 py-3 font-mono text-xs text-gray-700 whitespace-pre-wrap" x-html="previewText()"></div>
                    </div>

                    <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-5 py-3 text-sm font-medium text-white shadow-sm transition hover:bg-green-700 disabled:opacity-50"
                            :disabled="selectedContacts.length === 0 || messageBody.trim().length === 0">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.125.558 4.126 1.534 5.87L0 24l6.305-1.517A11.955 11.955 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.818 9.818 0 0 1-5.032-1.383l-.36-.214-3.742.9.934-3.649-.234-.374A9.818 9.818 0 1 1 12 21.818z"/>
                        </svg>
                        Generate WhatsApp Links
                        <span x-show="selectedContacts.length > 0" x-text="'(' + selectedContacts.length + ')'" class="rounded-full bg-green-500 px-2 py-0.5 text-xs"></span>
                    </button>
                </div>

                {{-- Right: contact picker --}}
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-700">Select contacts</p>
                        <button type="button" @click="toggleAll()"
                                class="text-xs font-medium text-green-600 hover:text-green-800"
                                x-text="selectedContacts.length === contacts.length ? 'Deselect all' : 'Select all'">
                        </button>
                    </div>

                    <input type="text" x-model="search" placeholder="Search by name or phone…"
                           class="mb-3 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm outline-none transition focus:border-green-400" />

                    <div class="max-h-[420px] space-y-1 overflow-y-auto">
                        @foreach ($contacts as $contact)
                            @if ($contact->phone)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition"
                                       :class="selectedContacts.includes({{ $contact->id }}) ? 'border-green-400 bg-green-50' : 'border-gray-100 hover:border-green-200'"
                                       x-show="matchesSearch({{ json_encode(['id' => $contact->id, 'name' => $contact->full_name, 'phone' => $contact->phone, 'company' => $contact->company?->name ?? '']) }})">
                                    <input type="checkbox" name="contact_ids[]" value="{{ $contact->id }}"
                                           @change="toggle({{ $contact->id }})"
                                           :checked="selectedContacts.includes({{ $contact->id }})"
                                           {{ in_array($contact->id, (array) $preselected) ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500" />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-800">{{ $contact->full_name }}</p>
                                        <p class="truncate text-xs text-gray-400">{{ $contact->phone }}{{ $contact->company ? ' · ' . $contact->company->name : '' }}</p>
                                    </div>
                                </label>
                            @else
                                <div class="flex items-center gap-3 rounded-xl border border-dashed border-gray-100 p-3 opacity-50"
                                     x-show="matchesSearch({{ json_encode(['id' => $contact->id, 'name' => $contact->full_name, 'phone' => '', 'company' => $contact->company?->name ?? '']) }})">
                                    <div class="h-4 w-4 rounded border border-gray-200"></div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm text-gray-400">{{ $contact->full_name }}</p>
                                        <p class="text-xs text-gray-300">No phone number</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @error('contact_ids')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-4 border-t border-gray-100 pt-4 text-xs text-gray-400">
                        <span x-text="selectedContacts.length"></span> contact(s) selected
                        · Only contacts with a phone number can receive WhatsApp messages.
                    </div>
                </div>
            </form>
        </div>
    </div>

    @php
        $contactsData = $contacts->map(fn ($c) => [
            'id'      => $c->id,
            'name'    => $c->full_name,
            'phone'   => $c->phone,
            'company' => $c->company?->name ?? '',
        ]);
    @endphp
    <script>
        function whatsappCompose() {
            return {
                type: '{{ old('type', 'custom') }}',
                templateId: '',
                messageBody: `{{ old('message_body', '') }}`,
                search: '',
                selectedContacts: @json(array_map('intval', (array) $preselected)),
                contacts: @json($contactsData),
                templates: @json($templates->keyBy('id')),

                init() {
                    @if ($preselected)
                        this.selectedContacts = [{{ (int) $preselected }}];
                    @endif
                },

                toggle(id) {
                    if (this.selectedContacts.includes(id)) {
                        this.selectedContacts = this.selectedContacts.filter(i => i !== id);
                    } else {
                        this.selectedContacts.push(id);
                    }
                },

                toggleAll() {
                    const visible = this.contacts.filter(c => c.phone && this.matchesSearch(c));
                    const visibleIds = visible.map(c => c.id);
                    const allSelected = visibleIds.every(id => this.selectedContacts.includes(id));
                    if (allSelected) {
                        this.selectedContacts = this.selectedContacts.filter(id => !visibleIds.includes(id));
                    } else {
                        visibleIds.forEach(id => { if (!this.selectedContacts.includes(id)) this.selectedContacts.push(id); });
                    }
                },

                matchesSearch(contact) {
                    if (!this.search) return true;
                    const q = this.search.toLowerCase();
                    return contact.name.toLowerCase().includes(q)
                        || (contact.phone && contact.phone.toLowerCase().includes(q))
                        || (contact.company && contact.company.toLowerCase().includes(q));
                },

                loadTemplate() {
                    if (!this.templateId) return;
                    const tpl = this.templates[this.templateId];
                    if (tpl) {
                        this.messageBody = tpl.message_body;
                        this.type = tpl.type;
                    }
                },

                previewText() {
                    const firstId = this.selectedContacts[0];
                    const contact = this.contacts.find(c => c.id === firstId);
                    if (!contact) return '';
                    let body = this.messageBody;
                    body = body.replace(/\{\{name\}\}/g, contact.name);
                    body = body.replace(/\{\{company\}\}/g, contact.company);
                    return body.replace(/\n/g, '<br>');
                },
            };
        }
    </script>
</x-layouts::app>
