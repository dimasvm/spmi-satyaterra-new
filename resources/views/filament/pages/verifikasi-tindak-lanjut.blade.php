<x-filament-panels::page>
    @php
        $stats = $this->stats();
        $counts = $this->tabCounts();
        $actions = $this->correctiveActions();
        $selectedAction = $this->selectedCorrectiveAction();
        $selectedFinding = $selectedAction?->finding;
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <x-filament::badge color="primary">Review Tindak Lanjut</x-filament::badge>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">Verifikasi Tindak Lanjut</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Validasi bukti perbaikan unit dan tutup temuan jika tindakan sudah memadai.</p>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Menunggu verifikasi</div>
                    <div class="mt-2 text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ number_format($stats['submitted'], 0, ',', '.') }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Sedang ditinjau</div>
                    <div class="mt-2 text-2xl font-semibold text-info-600 dark:text-info-400">{{ number_format($stats['in_review'], 0, ',', '.') }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Terlambat</div>
                    <div class="mt-2 text-2xl font-semibold text-danger-600 dark:text-danger-400">{{ number_format($stats['overdue'], 0, ',', '.') }}</div>
                </div>
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
                <label for="verification-search" class="text-sm font-medium text-gray-700 dark:text-gray-200">Cari tindak lanjut</label>
                <x-filament::input.wrapper class="mt-2" prefix-icon="heroicon-o-magnifying-glass">
                    <x-filament::input
                        id="verification-search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari nomor temuan, unit, atau rencana tindakan"
                    />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        <div class="grid gap-4">
            @forelse ($actions as $action)
                @php($finding = $action->finding)
                <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-filament::badge color="gray">{{ $finding?->finding_number ?? 'Temuan' }}</x-filament::badge>
                                <x-filament::badge color="info">{{ $finding?->audit?->auditeeUnit?->name ?? '-' }}</x-filament::badge>
                                <x-filament::badge :color="$action->status?->getColor() ?? 'gray'">{{ $action->status?->getLabel() ?? '-' }}</x-filament::badge>
                            </div>
                            <h3 class="mt-3 text-base font-semibold text-gray-950 dark:text-white">{{ $finding?->standardIndicator?->code ?? '-' }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ str($action->action_plan)->limit(180) }}</p>
                            <div class="mt-4 grid gap-3 text-sm md:grid-cols-4">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Target</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $action->target_date?->format('d M Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">PIC</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $action->picUser?->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Bukti</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $action->evidences->count() }} item</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Dikirim</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $action->submitted_at?->format('d M Y H:i') ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <x-filament::button type="button" size="sm" wire:click="openDetail({{ $action->id }})">Validasi</x-filament::button>
                    </div>
                </div>
            @empty
                <x-filament::section>
                    <div class="py-10 text-center">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Semua temuan sudah ditindaklanjuti.</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada tindak lanjut yang menunggu verifikasi pada tab atau pencarian ini.</p>
                    </div>
                </x-filament::section>
            @endforelse

            <div>
                {{ $actions->links() }}
            </div>
        </div>
    </div>

    @if ($isDetailOpen && $selectedAction)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/50 px-4 py-6 sm:items-center">
            <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-xl bg-white shadow-xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10">
                <div class="space-y-6 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex flex-wrap gap-2">
                                <x-filament::badge color="gray">{{ $selectedFinding?->finding_number ?? 'Temuan' }}</x-filament::badge>
                                <x-filament::badge :color="$selectedAction->status?->getColor() ?? 'gray'">{{ $selectedAction->status?->getLabel() ?? '-' }}</x-filament::badge>
                            </div>
                            <h3 class="mt-3 text-lg font-semibold text-gray-950 dark:text-white">Review Tindak Lanjut</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $selectedFinding?->description ?? '-' }}</p>
                        </div>
                        <button type="button" wire:click="closeDetail" class="rounded-lg px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">Tutup</button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Root cause</div>
                            <div class="mt-1 text-sm text-gray-950 dark:text-white">{{ $selectedAction->root_cause_analysis ?: '-' }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Rencana perbaikan</div>
                            <div class="mt-1 text-sm text-gray-950 dark:text-white">{{ $selectedAction->action_plan ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Bukti perbaikan</h4>
                        <div class="mt-3 grid gap-2">
                            @forelse ($selectedAction->evidences as $evidence)
                                <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                                    <div class="font-medium text-gray-950 dark:text-white">{{ $evidence->file_name ?: $evidence->external_url ?: 'Bukti' }}</div>
                                    <div class="mt-1 text-gray-500 dark:text-gray-400">{{ $evidence->description ?: '-' }}</div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500 dark:text-gray-400">Belum ada bukti.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Riwayat review</h4>
                        <div class="mt-3 grid gap-2">
                            @forelse ($selectedAction->reviews as $review)
                                <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-800">
                                    <x-filament::badge :color="$review->status?->getColor() ?? 'gray'">{{ $review->status?->getLabel() ?? '-' }}</x-filament::badge>
                                    <div class="mt-2 text-gray-700 dark:text-gray-300">{{ $review->notes ?: '-' }}</div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500 dark:text-gray-400">Belum ada review.</div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="review-notes">Catatan reviewer</label>
                        <x-filament::input.wrapper class="mt-2">
                            <textarea id="review-notes" wire:model="reviewNotes" rows="3" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none focus:ring-0 dark:text-white"></textarea>
                        </x-filament::input.wrapper>
                        @error('reviewNotes') <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-wrap justify-end gap-2">
                        <x-filament::button type="button" color="gray" wire:click="closeDetail">Batal</x-filament::button>
                        <x-filament::button type="button" color="warning" wire:click="requestRevision">Minta Revisi</x-filament::button>
                        <x-filament::button type="button" color="success" wire:click="accept">Terima Perbaikan</x-filament::button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
