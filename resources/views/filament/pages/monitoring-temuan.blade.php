<x-filament-panels::page>
    @php
        $stats = $this->stats();
        $findings = $this->findings();
        $selectedFinding = $this->selectedFinding();
        $selectedAction = $selectedFinding?->latestCorrectiveAction;
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <x-filament::badge color="primary">Monitoring LPM</x-filament::badge>
            <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">Monitoring Temuan</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Pantau status temuan, tindak lanjut, dan keterlambatan per unit/periode.</p>

            <div class="mt-6 grid gap-4 md:grid-cols-6">
                @foreach ([
                    ['Total', 'total', 'gray'],
                    ['Terbuka', 'open', 'gray'],
                    ['Menunggu', 'waiting', 'warning'],
                    ['Revisi', 'revision', 'danger'],
                    ['Selesai', 'closed', 'success'],
                    ['Terlambat', 'overdue', 'danger'],
                ] as [$label, $key, $color])
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</div>
                        <div @class([
                            'mt-2 text-2xl font-semibold',
                            'text-gray-950 dark:text-white' => $color === 'gray',
                            'text-warning-600 dark:text-warning-400' => $color === 'warning',
                            'text-danger-600 dark:text-danger-400' => $color === 'danger',
                            'text-success-600 dark:text-success-400' => $color === 'success',
                        ])>{{ number_format($stats[$key] ?? 0, 0, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="grid gap-4 md:grid-cols-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="monitoring-search">Pencarian</label>
                    <x-filament::input.wrapper class="mt-2" prefix-icon="heroicon-o-magnifying-glass">
                        <x-filament::input
                            id="monitoring-search"
                            type="search"
                            wire:model.live.debounce.400ms="search"
                            placeholder="Nomor, unit, indikator"
                        />
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="unit-filter">Unit</label>
                    <x-filament::input.wrapper class="mt-2">
                        <x-filament::input.select id="unit-filter" wire:model.live="selectedUnitId">
                            <option value="">Semua unit</option>
                            @foreach ($this->unitOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="period-filter">Periode AMI</label>
                    <x-filament::input.wrapper class="mt-2">
                        <x-filament::input.select id="period-filter" wire:model.live="selectedAmiPeriodId">
                            <option value="">Semua periode</option>
                            @foreach ($this->periodOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="category-filter">Kategori</label>
                    <x-filament::input.wrapper class="mt-2">
                        <x-filament::input.select id="category-filter" wire:model.live="selectedCategory">
                            <option value="">Semua kategori</option>
                            @foreach ($this->categoryOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200" for="status-filter">Status</label>
                    <x-filament::input.wrapper class="mt-2">
                        <x-filament::input.select id="status-filter" wire:model.live="selectedStatus">
                            <option value="">Semua status</option>
                            @foreach ($this->statusOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-4">
            @forelse ($findings as $finding)
                <div @class([
                    'rounded-lg border bg-white p-5 dark:bg-gray-900',
                    'border-danger-300 dark:border-danger-800' => $finding->due_date?->isPast() && $finding->status !== \App\Enums\AmiFindingStatus::Closed,
                    'border-gray-200 dark:border-gray-800' => ! ($finding->due_date?->isPast() && $finding->status !== \App\Enums\AmiFindingStatus::Closed),
                ])>
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-filament::badge color="gray">{{ $finding->finding_number ?? 'Temuan' }}</x-filament::badge>
                                <x-filament::badge color="info">{{ $finding->audit?->auditeeUnit?->name ?? '-' }}</x-filament::badge>
                                <x-filament::badge :color="$finding->category?->getColor() ?? 'gray'">{{ $finding->category?->getLabel() ?? '-' }}</x-filament::badge>
                                <x-filament::badge :color="$finding->status?->getColor() ?? 'gray'">{{ $finding->status?->getLabel() ?? '-' }}</x-filament::badge>
                            </div>
                            <h3 class="mt-3 text-base font-semibold text-gray-950 dark:text-white">{{ $finding->standardIndicator?->code ?? '-' }}</h3>
                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ str($finding->description)->limit(180) }}</p>
                            <div class="mt-4 grid gap-3 text-sm md:grid-cols-4">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Periode</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $finding->audit?->amiPeriod?->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Tenggat</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $finding->due_date?->format('d M Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Tindak lanjut</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $finding->latestCorrectiveAction?->status?->getLabel() ?? 'Belum ada' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">PIC</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $finding->latestCorrectiveAction?->picUser?->name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <x-filament::button type="button" size="sm" color="gray" wire:click="openDetail({{ $finding->id }})">Buka Detail</x-filament::button>
                    </div>
                </div>
            @empty
                <x-filament::section>
                    <div class="py-10 text-center">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Tidak ada temuan sesuai filter.</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Ubah filter atau kata kunci pencarian untuk melihat data lain.</p>
                    </div>
                </x-filament::section>
            @endforelse

            <div>
                {{ $findings->links() }}
            </div>
        </div>
    </div>

    @if ($isDetailOpen && $selectedFinding)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/50 px-4 py-6 sm:items-center">
            <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <x-filament::badge :color="$selectedFinding->status?->getColor() ?? 'gray'">{{ $selectedFinding->status?->getLabel() ?? '-' }}</x-filament::badge>
                        <h3 class="mt-3 text-lg font-semibold text-gray-950 dark:text-white">{{ $selectedFinding->finding_number ?? 'Detail Temuan' }}</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $selectedFinding->description }}</p>
                    </div>
                    <button type="button" wire:click="closeDetail" class="rounded-lg px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">Tutup</button>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Rekomendasi</div>
                        <div class="mt-1 text-sm text-gray-950 dark:text-white">{{ $selectedFinding->recommendation ?: '-' }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Rencana perbaikan</div>
                        <div class="mt-1 text-sm text-gray-950 dark:text-white">{{ $selectedAction?->action_plan ?: '-' }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Root cause</div>
                        <div class="mt-1 text-sm text-gray-950 dark:text-white">{{ $selectedAction?->root_cause_analysis ?: '-' }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Bukti</div>
                        <div class="mt-1 text-sm text-gray-950 dark:text-white">{{ $selectedAction?->evidences?->count() ?? 0 }} item</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
