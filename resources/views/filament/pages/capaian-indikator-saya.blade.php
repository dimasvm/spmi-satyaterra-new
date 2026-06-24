<x-filament-panels::page>
    @php
        $summary = $this->headerSummary();
        $counts = $this->tabCounts();
        $assignments = $this->assignments();
        $formAssignment = $this->formAssignment();
        $formIndicator = $formAssignment?->standardIndicator;
        $formType = $this->formIndicatorType();
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <x-filament::badge color="primary">
                        Pelaksanaan
                    </x-filament::badge>

                    <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        Capaian Indikator Saya
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        {{ $summary['unit'] }} · {{ $summary['period'] }}
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

                    @if(auth()->user()?->isAdminLpm() || auth()->user()?->isSuperAdmin())
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
                    @endif
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Progress validasi</div>
                    <div class="mt-2 flex items-end justify-between gap-3">
                        <div class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $summary['progress'] }}%</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $summary['completed'] }}/{{ $summary['total'] }}</div>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-2 rounded-full bg-primary-600" style="width: {{ min($summary['progress'], 100) }}%"></div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total tugas</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total'], 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ number_format($summary['filled'], 0, ',', '.') }} sudah mulai diisi</div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Deadline terdekat</div>
                    <div @class([
                        'mt-2 text-lg font-semibold',
                        'text-danger-600 dark:text-danger-400' => $summary['nearest_deadline_warning'],
                        'text-gray-950 dark:text-white' => ! $summary['nearest_deadline_warning'],
                    ])>
                        {{ $summary['nearest_deadline'] ?? '-' }}
                    </div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $summary['nearest_deadline_warning'] ? 'Perlu diprioritaskan' : 'Tidak ada yang mendesak' }}
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Dikembalikan</div>
                    <div class="mt-2 text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ number_format($counts['returned'], 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perlu submit ulang</div>
                </div>
            </div>
        </x-filament::section>

        <div class="overflow-x-auto pb-2">
            <div class="flex min-w-max gap-2">
                @foreach ($this->statusTabs() as $tab => $label)
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
                <label for="achievement-search" class="text-sm font-medium text-gray-700 dark:text-gray-200">Cari capaian</label>
                <x-filament::input.wrapper class="mt-2" prefix-icon="heroicon-o-magnifying-glass">
                    <x-filament::input
                        id="achievement-search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari unit, standar, kode, atau indikator"
                    />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        <div class="grid gap-4">
            @forelse ($assignments as $assignment)
                @php
                    $indicator = $assignment->standardIndicator;
                    $achievement = $assignment->latestAchievement;
                    $submissionStatus = $achievement?->submission_status;
                    $achievementStatus = $achievement?->achievement_status;
                    $isEditable = $achievement === null || in_array($submissionStatus, [\App\Enums\SubmissionStatus::Draft, \App\Enums\SubmissionStatus::Returned], true);
                    $evidenceCount = $achievement?->evidences?->count() ?? 0;
                @endphp

                <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-filament::badge color="gray">
                                    {{ $indicator?->qualityStandard?->name ?? 'Standar belum tersedia' }}
                                </x-filament::badge>
                                <x-filament::badge color="info">
                                    {{ $indicator?->code ?? '-' }}
                                </x-filament::badge>
                                @if ($submissionStatus)
                                    <x-filament::badge :color="$submissionStatus->getColor()">
                                        {{ $submissionStatus->getLabel() }}
                                    </x-filament::badge>
                                @else
                                    <x-filament::badge color="danger">
                                        Belum Diisi
                                    </x-filament::badge>
                                @endif
                                @if ($achievementStatus)
                                    <x-filament::badge :color="$achievementStatus->getColor()">
                                        {{ $achievementStatus->getLabel() }}
                                    </x-filament::badge>
                                @endif
                            </div>

                            <h3 class="mt-3 text-base font-semibold text-gray-950 dark:text-white">
                                {{ $indicator?->statement ?? 'Indikator belum tersedia' }}
                            </h3>

                            <div class="mt-4 grid gap-3 text-sm md:grid-cols-4">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Target</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $this->targetSummary($assignment) }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Tenggat</div>
                                    <div @class([
                                        'mt-1 font-medium',
                                        'text-danger-600 dark:text-danger-400' => $assignment->due_date?->isPast() && $submissionStatus !== \App\Enums\SubmissionStatus::Validated,
                                        'text-gray-950 dark:text-white' => ! ($assignment->due_date?->isPast() && $submissionStatus !== \App\Enums\SubmissionStatus::Validated),
                                    ])>
                                        {{ $assignment->due_date?->format('d M Y') ?? '-' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Jumlah bukti</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ number_format($evidenceCount, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Unit</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $assignment->unit?->name ?? '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                            @if ($isEditable)
                                <x-filament::button type="button" size="sm" wire:click="openAchievementForm({{ $assignment->id }})">
                                    {{ $achievement === null ? 'Isi Capaian' : 'Edit Draf' }}
                                </x-filament::button>
                            @else
                                <x-filament::button type="button" size="sm" color="gray" wire:click="viewReview({{ $assignment->id }})">
                                    Lihat Review
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <x-filament::section>
                    <div class="py-10 text-center">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Belum ada capaian yang sesuai.
                        </h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Tidak ada indikator pada tab, periode, atau pencarian ini.
                        </p>
                    </div>
                </x-filament::section>
            @endforelse

            <div>
                {{ $assignments->links() }}
            </div>
        </div>
    </div>

    @if ($isAchievementModalOpen && $formAssignment)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/50 px-4 py-6 sm:items-center">
            <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl bg-white shadow-xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10">
                <form wire:submit="saveDraft" class="space-y-6 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <x-filament::badge color="primary">
                                {{ $formIndicator?->code ?? '-' }}
                            </x-filament::badge>
                            <h3 class="mt-3 text-lg font-semibold text-gray-950 dark:text-white">
                                Isi Capaian Indikator
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                {{ $formIndicator?->statement ?? '-' }}
                            </p>
                        </div>
                        <button type="button" wire:click="closeAchievementForm" class="rounded-lg px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                            Tutup
                        </button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Target</div>
                            <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $this->targetSummary() }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Jenis indikator</div>
                            <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $formType?->getLabel() ?? '-' }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Bukti wajib</div>
                            <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $formIndicator?->evidence_required ? 'Ya' : 'Tidak' }}</div>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="realization-value" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Realisasi angka/persen
                            </label>
                            <x-filament::input.wrapper class="mt-2">
                                <x-filament::input id="realization-value" type="number" step="0.01" wire:model.live="realizationValue" />
                            </x-filament::input.wrapper>
                            @error('realizationValue')
                                <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="achievement-status" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Status capaian
                            </label>
                            <x-filament::input.wrapper class="mt-2">
                                <x-filament::input.select id="achievement-status" wire:model="achievementStatus">
                                    <option value="">Otomatis jika memungkinkan</option>
                                    @foreach (\App\Enums\AchievementStatus::cases() as $status)
                                        <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div class="md:col-span-2">
                            <label for="realization-text" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Realisasi naratif / checklist
                            </label>
                            <x-filament::input.wrapper class="mt-2">
                                <textarea id="realization-text" wire:model="realizationText" rows="4" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>
                            </x-filament::input.wrapper>
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Catatan
                            </label>
                            <x-filament::input.wrapper class="mt-2">
                                <textarea id="notes" wire:model="notes" rows="3" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>
                            </x-filament::input.wrapper>
                            @error('notes')
                                <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="evidence-files" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Upload bukti
                                </label>
                                <input
                                    id="evidence-files"
                                    type="file"
                                    multiple
                                    wire:model="evidenceFiles"
                                    class="mt-2 block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-600 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-primary-500 dark:text-gray-300"
                                >
                                @error('evidenceFiles')
                                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                                @error('evidenceFiles.*')
                                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="external-url" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    External URL
                                </label>
                                <x-filament::input.wrapper class="mt-2">
                                    <x-filament::input id="external-url" type="url" wire:model="externalUrl" placeholder="https://..." />
                                </x-filament::input.wrapper>
                                @error('externalUrl')
                                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="evidence-description" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    Deskripsi bukti
                                </label>
                                <x-filament::input.wrapper class="mt-2">
                                    <textarea id="evidence-description" wire:model="evidenceDescription" rows="2" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>
                                </x-filament::input.wrapper>
                                @error('evidenceDescription')
                                    <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <x-filament::button type="button" color="gray" wire:click="closeAchievementForm">
                            Batal
                        </x-filament::button>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <x-filament::button type="submit" color="gray">
                                Simpan Draft
                            </x-filament::button>
                            <x-filament::button type="button" wire:click="submitAchievement">
                                Submit
                            </x-filament::button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-filament-panels::page>
