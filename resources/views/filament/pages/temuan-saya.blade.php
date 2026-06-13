<x-filament-panels::page>
    @php
        $stats = $this->stats();
        $counts = $this->tabCounts();
        $findings = $this->findings();
        $selectedFinding = $this->selectedFinding();
        $correctiveAction = $selectedFinding?->latestCorrectiveAction;
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <x-filament::badge color="primary">Ticketing Temuan</x-filament::badge>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">Temuan Saya</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ auth()->user()?->unit?->name ?? 'Unit belum tersedia' }}
            </p>

            <div class="mt-6 grid gap-4 md:grid-cols-6">
                @foreach ([
                    ['Terbuka', 'open', 'gray'],
                    ['Dalam Proses', 'in_progress', 'info'],
                    ['Menunggu Verifikasi', 'waiting_verification', 'warning'],
                    ['Perlu Revisi', 'need_revision', 'danger'],
                    ['Selesai', 'closed', 'success'],
                    ['Terlambat', 'overdue', 'danger'],
                ] as [$label, $key, $color])
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</div>
                        <div @class([
                            'mt-2 text-2xl font-semibold',
                            'text-gray-950 dark:text-white' => $color === 'gray',
                            'text-info-600 dark:text-info-400' => $color === 'info',
                            'text-warning-600 dark:text-warning-400' => $color === 'warning',
                            'text-danger-600 dark:text-danger-400' => $color === 'danger',
                            'text-success-600 dark:text-success-400' => $color === 'success',
                        ])>{{ number_format($stats[$key] ?? 0, 0, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <div class="overflow-x-auto pb-2">
            <div class="flex min-w-max gap-2">
                @foreach ($this->tabs() as $tab => $label)
                    <button type="button" wire:click="setTab('{{ $tab }}')" @class([
                        'inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition',
                        'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-500/70 dark:bg-primary-950/30 dark:text-primary-300' => $activeTab === $tab,
                        'border-gray-200 bg-white text-gray-600 hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-700' => $activeTab !== $tab,
                    ])>
                        <span>{{ $label }}</span>
                        <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $counts[$tab] ?? 0 }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <x-filament::section>
            <div class="max-w-xl">
                <label for="finding-search" class="text-sm font-medium text-gray-700 dark:text-gray-200">Cari temuan</label>
                <x-filament::input.wrapper class="mt-2" prefix-icon="heroicon-o-magnifying-glass">
                    <x-filament::input
                        id="finding-search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari nomor temuan, unit, indikator, atau deskripsi"
                    />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        <div class="grid gap-4">
            @forelse ($findings as $finding)
                @php($action = $finding->latestCorrectiveAction)
                <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-filament::badge color="gray">{{ $finding->finding_number ?? 'Temuan' }}</x-filament::badge>
                                <x-filament::badge :color="$finding->category?->getColor() ?? 'gray'">{{ $finding->category?->getLabel() ?? '-' }}</x-filament::badge>
                                <x-filament::badge :color="$finding->status?->getColor() ?? 'gray'">{{ $finding->status?->getLabel() ?? '-' }}</x-filament::badge>
                                @if ($action?->status)
                                    <x-filament::badge :color="$action->status->getColor()">{{ $action->status->getLabel() }}</x-filament::badge>
                                @endif
                            </div>
                            <h3 class="mt-3 text-base font-semibold text-gray-950 dark:text-white">{{ $finding->standardIndicator?->code ?? '-' }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ str($finding->description)->limit(170) }}</p>
                            <div class="mt-4 grid gap-3 text-sm md:grid-cols-3">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Rekomendasi</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ str($finding->recommendation ?: '-')->limit(70) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Deadline</div>
                                    <div @class([
                                        'mt-1 font-medium',
                                        'text-danger-600 dark:text-danger-400' => $finding->due_date?->isPast() && $finding->status !== \App\Enums\AmiFindingStatus::Closed,
                                        'text-gray-950 dark:text-white' => ! ($finding->due_date?->isPast() && $finding->status !== \App\Enums\AmiFindingStatus::Closed),
                                    ])>{{ $finding->due_date?->format('d M Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Target perbaikan</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $action?->target_date?->format('d M Y') ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <x-filament::button type="button" size="sm" wire:click="openTicket({{ $finding->id }})">
                            Submit Tindak Lanjut
                        </x-filament::button>
                    </div>
                </div>
            @empty
                <x-filament::section>
                    <div class="py-10 text-center">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Semua temuan sudah ditindaklanjuti.</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada temuan pada tab atau pencarian ini.</p>
                    </div>
                </x-filament::section>
            @endforelse

            <div>
                {{ $findings->links() }}
            </div>
        </div>
    </div>

    @if ($isTicketOpen && $selectedFinding)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/50 px-4 py-6 sm:items-center">
            <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-xl bg-white shadow-xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10">
                <form wire:submit="saveDraft" class="space-y-6 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap gap-2">
                                <x-filament::badge color="gray">{{ $selectedFinding->finding_number ?? 'Temuan' }}</x-filament::badge>
                                <x-filament::badge :color="$selectedFinding->category?->getColor() ?? 'gray'">{{ $selectedFinding->category?->getLabel() ?? '-' }}</x-filament::badge>
                            </div>
                            <h3 class="mt-3 text-lg font-semibold text-gray-950 dark:text-white">Detail Temuan</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $selectedFinding->description }}</p>
                        </div>
                        <button type="button" wire:click="closeTicket" class="rounded-lg px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">Tutup</button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Indikator</div>
                            <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedFinding->standardIndicator?->code ?? '-' }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Due date</div>
                            <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $selectedFinding->due_date?->format('d M Y') ?? '-' }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Bukti</div>
                            <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $correctiveAction?->evidences?->count() ?? 0 }} item</div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 text-sm dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Rekomendasi auditor</div>
                        <div class="mt-1 text-gray-950 dark:text-white">{{ $selectedFinding->recommendation ?: '-' }}</div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="root-cause">Root cause analysis</label>
                            <x-filament::input.wrapper class="mt-2">
                                <textarea id="root-cause" wire:model="rootCauseAnalysis" rows="4" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none focus:ring-0 dark:text-white"></textarea>
                            </x-filament::input.wrapper>
                            @error('rootCauseAnalysis') <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="action-plan">Rencana perbaikan</label>
                            <x-filament::input.wrapper class="mt-2">
                                <textarea id="action-plan" wire:model="actionPlan" rows="5" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none focus:ring-0 dark:text-white"></textarea>
                            </x-filament::input.wrapper>
                            @error('actionPlan') <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="pic-user">PIC</label>
                            <x-filament::input.wrapper class="mt-2">
                                <x-filament::input.select id="pic-user" wire:model="picUserId">
                                    <option value="">Pilih PIC</option>
                                    @foreach ($this->picOptions() as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="target-date">Target date</label>
                            <x-filament::input.wrapper class="mt-2">
                                <x-filament::input id="target-date" type="date" wire:model="targetDate" />
                            </x-filament::input.wrapper>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="evidence-files">Upload bukti</label>
                            <x-filament::input.wrapper class="mt-2">
                                <input id="evidence-files" type="file" wire:model="evidenceFiles" multiple class="block w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-200" />
                            </x-filament::input.wrapper>
                            @error('evidenceFiles') <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="external-url">Tautan bukti</label>
                            <x-filament::input.wrapper class="mt-2">
                                <x-filament::input id="external-url" type="url" wire:model="externalUrl" />
                            </x-filament::input.wrapper>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="evidence-description">Deskripsi bukti</label>
                            <x-filament::input.wrapper class="mt-2">
                                <textarea id="evidence-description" wire:model="evidenceDescription" rows="2" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none focus:ring-0 dark:text-white"></textarea>
                            </x-filament::input.wrapper>
                        </div>
                    </div>

                    @if ($correctiveAction?->reviews?->isNotEmpty())
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Riwayat review</h4>
                            <div class="mt-3 grid gap-2">
                                @foreach ($correctiveAction->reviews as $review)
                                    <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                                        <x-filament::badge :color="$review->status?->getColor() ?? 'gray'">{{ $review->status?->getLabel() ?? '-' }}</x-filament::badge>
                                        <div class="mt-2 text-gray-700 dark:text-gray-300">{{ $review->notes ?: '-' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-wrap justify-end gap-2">
                        <x-filament::button type="button" color="gray" wire:click="closeTicket">Batal</x-filament::button>
                        <x-filament::button type="submit" color="gray">Simpan Draft</x-filament::button>
                        <x-filament::button type="button" wire:click="submitVerification">Submit Verifikasi</x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-filament-panels::page>
