<x-filament-panels::page>
    @php
        $stats = $this->stats();
        $counts = $this->tabCounts();
        $achievements = $this->achievements();
        $reviewingAchievement = $this->reviewingAchievement();
        $reviewingAssignment = $reviewingAchievement?->assignment;
        $reviewingIndicator = $reviewingAssignment?->standardIndicator;
        $validationRequired = (bool) \App\Models\SystemSetting::get('achievement_validation_required', true);
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <x-filament::badge color="primary">
                        Pelaksanaan / SPMI
                    </x-filament::badge>

                    <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        Inbox Validasi Capaian
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Periksa capaian indikator dan bukti yang dikirim unit.
                    </p>
                </div>

                <div class="flex w-full max-w-lg flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="flex-1">
                        <label for="spmi-period" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Periode SPMI
                        </label>
                        <x-filament::input.wrapper class="mt-2">
                            <x-filament::input.select id="spmi-period" wire:model.live="selectedSpmiPeriodId">
                                @foreach ($this->periodOptions() as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="flex-1">
                        <label for="unit-filter" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Unit
                        </label>
                        <x-filament::input.wrapper class="mt-2">
                            <x-filament::input.select id="unit-filter" wire:model.live="selectedUnitId">
                                <option value="">Semua Unit</option>
                                @foreach ($this->unitOptions() as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs text-gray-500 dark:text-gray-400">Menunggu Validasi</div>
                <div class="mt-2 text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ number_format($stats['submitted'], 0, ',', '.') }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs text-gray-500 dark:text-gray-400">Dikembalikan</div>
                <div class="mt-2 text-2xl font-semibold text-danger-600 dark:text-danger-400">{{ number_format($stats['returned'], 0, ',', '.') }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs text-gray-500 dark:text-gray-400">Tervalidasi</div>
                <div class="mt-2 text-2xl font-semibold text-success-600 dark:text-success-400">{{ number_format($stats['validated'], 0, ',', '.') }}</div>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs text-gray-500 dark:text-gray-400">Ditolak / Bermasalah</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($stats['rejected'], 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="overflow-x-auto pb-2">
            <div class="flex min-w-max gap-2">
                @foreach ($this->tabs() as $tab => $label)
                    <button
                        type="button"
                        wire:click="setTab('{{ $tab }}')"
                        @class([
                            'inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition',
                            'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-500/70 dark:bg-primary-950/30 dark:text-primary-300' => $activeTab === $tab,
                            'border-gray-200 bg-white text-gray-600 hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-700' => $activeTab !== $tab,
                        ])
                    >
                        <span>{{ $label }}</span>
                        <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ $counts[$tab] ?? 0 }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <x-filament::section>
            <div class="max-w-xl">
                <label for="review-search" class="text-sm font-medium text-gray-700 dark:text-gray-200">Cari capaian</label>
                <x-filament::input.wrapper class="mt-2" prefix-icon="heroicon-o-magnifying-glass">
                    <x-filament::input
                        id="review-search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari unit, standar, kode, atau indikator"
                    />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        <div class="grid gap-4">
            @forelse ($achievements as $achievement)
                @php
                    $assignment = $achievement->assignment;
                    $indicator = $achievement->standard_indicator;
                @endphp

                <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-filament::badge color="gray">
                                    {{ $assignment?->unit?->name ?? '-' }}
                                </x-filament::badge>
                                <x-filament::badge color="info">
                                    {{ $indicator?->qualityStandard?->name ?? '-' }}
                                </x-filament::badge>
                                <x-filament::badge :color="$achievement->submission_status?->getColor()">
                                    {{ $achievement->submission_status?->getLabel() ?? '-' }}
                                </x-filament::badge>
                                @if ($achievement->achievement_status)
                                    <x-filament::badge :color="$achievement->achievement_status->getColor()">
                                        {{ $achievement->achievement_status->getLabel() }}
                                    </x-filament::badge>
                                @endif
                            </div>

                            <h3 class="mt-3 text-base font-semibold text-gray-950 dark:text-white">
                                {{ $indicator?->code }} · {{ $indicator?->statement }}
                            </h3>

                            <div class="mt-4 grid gap-3 text-sm md:grid-cols-4">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Target</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $this->targetSummary($achievement) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Realisasi</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $this->realizationSummary($achievement) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Jumlah bukti</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ number_format($achievement->evidences_count, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Dikirim</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">
                                        {{ $achievement->submittedBy?->name ?? '-' }}
                                        <span class="block text-xs font-normal text-gray-500 dark:text-gray-400">{{ $achievement->submitted_at?->format('d M Y H:i') ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <x-filament::button type="button" wire:click="openReview({{ $achievement->id }})">
                            {{ $validationRequired ? 'Validasi' : 'Detail' }}
                        </x-filament::button>
                    </div>
                </div>
            @empty
                <x-filament::section>
                    <div class="py-10 text-center">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Belum ada capaian yang perlu divalidasi.
                        </h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Semua capaian pada tab, periode, atau pencarian ini sudah tertangani.
                        </p>
                    </div>
                </x-filament::section>
            @endforelse

            <div>
                {{ $achievements->links() }}
            </div>
        </div>
    </div>

    @if ($isReviewModalOpen && $reviewingAchievement)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/50 px-4 py-6 sm:items-center">
            <div class="max-h-[90vh] w-full max-w-6xl overflow-y-auto rounded-xl bg-white shadow-xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10">
                <div class="space-y-6 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <x-filament::badge color="primary">
                                Review Capaian
                            </x-filament::badge>
                            <h3 class="mt-3 text-lg font-semibold text-gray-950 dark:text-white">
                                {{ $reviewingIndicator?->code }} · {{ $reviewingAssignment?->unit?->name }}
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                {{ $reviewingIndicator?->qualityStandard?->name }}
                            </p>
                        </div>
                        <button type="button" wire:click="closeReview" class="rounded-lg px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                            Tutup
                        </button>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="space-y-4">
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Standar</div>
                                <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $reviewingIndicator?->qualityStandard?->name ?? '-' }}</div>
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Indikator</div>
                                <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $reviewingIndicator?->statement ?? '-' }}</div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950/40">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Target</div>
                                    <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $this->targetSummary($reviewingAchievement) }}</div>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950/40">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Realisasi</div>
                                    <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $this->realizationSummary($reviewingAchievement) }}</div>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-950/40">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Status capaian</div>
                                    <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $reviewingAchievement->achievement_status?->getLabel() ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Realisasi naratif</div>
                                <p class="mt-2 text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $reviewingAchievement->realization_text ?: '-' }}</p>
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Catatan unit</div>
                                <p class="mt-2 text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $reviewingAchievement->notes ?: '-' }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-semibold text-gray-950 dark:text-white">Bukti Capaian</div>
                                    <x-filament::badge color="gray">{{ $reviewingAchievement->evidences->count() }}</x-filament::badge>
                                </div>

                                <div class="mt-4 grid gap-3">
                                    @forelse ($reviewingAchievement->evidences as $evidence)
                                        @php($url = $this->evidenceUrl($evidence))
                                        <div class="rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-medium text-gray-950 dark:text-white">{{ $this->evidenceName($evidence) }}</div>
                                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $evidence->description ?: 'Tanpa deskripsi' }}</div>
                                                </div>
                                                @if ($url)
                                                    <x-filament::button tag="a" :href="$url" target="_blank" size="xs" color="gray">
                                                        Buka
                                                    </x-filament::button>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                            Belum ada bukti.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                <div class="text-sm font-semibold text-gray-950 dark:text-white">Riwayat Review</div>
                                <div class="mt-4 space-y-3">
                                    @forelse ($reviewingAchievement->reviews->sortByDesc('created_at') as $review)
                                        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-950/40">
                                            <div class="flex items-center justify-between gap-3">
                                                <x-filament::badge :color="$review->status?->getColor()">{{ $review->status?->getLabel() ?? '-' }}</x-filament::badge>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $review->reviewed_at?->format('d M Y H:i') ?? $review->created_at?->format('d M Y H:i') }}</span>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ $review->notes ?: 'Tanpa catatan' }}</p>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $review->reviewer?->name ?? 'Belum ada reviewer' }}</div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat review.</p>
                                    @endforelse
                                </div>
                            </div>

                            @if ($validationRequired)
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                <label for="review-notes" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Catatan Validator
                                </label>
                                <x-filament::input.wrapper class="mt-2">
                                    <textarea id="review-notes" wire:model="reviewNotes" rows="4" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>
                                </x-filament::input.wrapper>
                                @error('reviewNotes')
                                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <x-filament::button type="button" color="gray" wire:click="closeReview">
                            {{ $validationRequired ? 'Batal' : 'Tutup' }}
                        </x-filament::button>
                        @if ($validationRequired)
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <x-filament::button type="button" color="danger" wire:click="rejectAchievement">
                                Tolak
                            </x-filament::button>
                            <x-filament::button type="button" color="warning" wire:click="returnAchievement">
                                Kembalikan untuk Revisi
                            </x-filament::button>
                            <x-filament::button type="button" color="success" wire:click="validateAchievement">
                                Validasi
                            </x-filament::button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
