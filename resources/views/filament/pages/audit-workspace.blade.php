<x-filament-panels::page>
    @php
        $summary = $this->summary();
        $checklists = $this->checklists();
        $findings = $this->findings();
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge color="primary">Ruang Kerja Audit</x-filament::badge>
                        <x-filament::badge :color="$summary['status']?->getColor() ?? 'gray'">
                            {{ $summary['status']?->getLabel() ?? '-' }}
                        </x-filament::badge>
                        <x-filament::badge :color="$this->roleColor()">
                            {{ $this->roleLabel() }}
                        </x-filament::badge>
                    </div>
                    <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $summary['unit'] }}
                    </h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        {{ $summary['period'] }} - Jadwal {{ $summary['scheduled_date'] }}
                    </p>
                </div>

                <div class="w-full max-w-xs">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Progres checklist</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ $summary['progress'] }}%</div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $summary['completed_checklists'] }}/{{ $summary['total_checklists'] }}
                        </div>
                    </div>
                    <div class="mt-3 h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-2 rounded-full bg-primary-600" style="width: {{ min($summary['progress'], 100) }}%"></div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="overflow-x-auto pb-2">
            <div class="flex min-w-max gap-2">
                @foreach ($this->tabs() as $tab => $label)
                    <button
                        type="button"
                        wire:click="setTab('{{ $tab }}')"
                        @class([
                            'inline-flex items-center rounded-lg border px-3 py-2 text-sm font-medium transition',
                            'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-500/70 dark:bg-primary-950/30 dark:text-primary-300' => $activeTab === $tab,
                            'border-gray-200 bg-white text-gray-600 hover:border-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-700' => $activeTab !== $tab,
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        @if ($activeTab === 'summary')
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Sesuai</div>
                    <div class="mt-2 text-2xl font-semibold text-success-600 dark:text-success-400">{{ $summary['conform'] }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Minor</div>
                    <div class="mt-2 text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ $summary['minor'] }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Mayor</div>
                    <div class="mt-2 text-2xl font-semibold text-danger-600 dark:text-danger-400">{{ $summary['major'] }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Observasi / OFI</div>
                    <div class="mt-2 text-2xl font-semibold text-info-600 dark:text-info-400">{{ $summary['observation'] + $summary['ofi'] }}</div>
                </div>
            </div>

            <x-filament::section>
                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Tim auditor</h3>
                        <div class="mt-4 grid gap-3">
                            @foreach ($this->record->auditorAssignments as $assignment)
                                <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                                    <div>
                                        <div class="text-sm font-medium text-gray-950 dark:text-white">{{ $assignment->user?->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $assignment->user?->email ?? '-' }}</div>
                                    </div>
                                    <x-filament::badge :color="$assignment->role?->getColor() ?? 'gray'">
                                        {{ $assignment->role?->getLabel() ?? '-' }}
                                    </x-filament::badge>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Status audit</h3>
                        <div class="mt-4 grid gap-3 text-sm">
                            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Temuan</div>
                                <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $summary['findings'] }} temuan</div>
                            </div>
                            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Finalisasi</div>
                                <div class="mt-1 font-medium text-gray-950 dark:text-white">
                                    {{ $this->record->finalized_at?->format('d M Y H:i') ?? 'Belum final' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif

        @if ($activeTab === 'checklists')
            <div class="grid gap-4">
                @forelse ($checklists as $checklist)
                    @php
                        $indicator = $checklist->standardIndicator;
                        $achievement = $this->unitAchievement($checklist);
                        $review = $this->latestFinalReview($checklist);
                        $evidenceCount = $achievement?->evidences?->count() ?? 0;
                    @endphp

                    <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-filament::badge color="gray">{{ $indicator?->qualityStandard?->name ?? 'Standar belum tersedia' }}</x-filament::badge>
                                    <x-filament::badge color="info">{{ $indicator?->code ?? '-' }}</x-filament::badge>
                                    @if ($checklist->assessment_result)
                                        <x-filament::badge :color="$checklist->assessment_result->getColor()">
                                            {{ $checklist->assessment_result->getLabel() }}
                                        </x-filament::badge>
                                    @else
                                        <x-filament::badge color="warning">Belum assessment</x-filament::badge>
                                    @endif
                                </div>

                                <h3 class="mt-3 text-base font-semibold text-gray-950 dark:text-white">
                                    {{ $indicator?->statement ?? 'Indikator belum tersedia' }}
                                </h3>

                                <div class="mt-4 grid gap-3 text-sm md:grid-cols-4">
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Target</div>
                                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $this->targetSummary($checklist) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Realisasi unit</div>
                                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $this->realizationSummary($checklist) }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Validasi LPM</div>
                                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $review?->status?->getLabel() ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Bukti</div>
                                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $evidenceCount }} file/link</div>
                                    </div>
                                </div>

                                @if (filled($checklist->auditor_notes))
                                    <div class="mt-4 rounded-lg bg-gray-50 p-3 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        {{ $checklist->auditor_notes }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                                @if ($this->canAssessChecklist($checklist))
                                    <x-filament::button type="button" size="sm" wire:click="openAssessment({{ $checklist->id }})">
                                        Simpan Assessment
                                    </x-filament::button>
                                @endif
                                @if ($this->canCreateFindingFromChecklist($checklist))
                                    <x-filament::button type="button" size="sm" color="warning" wire:click="openFindingFromChecklist({{ $checklist->id }})">
                                        Buat Temuan
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <x-filament::section>
                        <div class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            Checklist belum dibuat untuk audit ini.
                        </div>
                    </x-filament::section>
                @endforelse
            </div>
        @endif

        @if ($activeTab === 'evidence')
            <div class="grid gap-4">
                @foreach ($this->evidenceGroups() as $group)
                    <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                        <x-filament::badge color="gray">{{ $group['standard'] }}</x-filament::badge>
                        <h3 class="mt-3 text-base font-semibold text-gray-950 dark:text-white">{{ $group['indicator'] }}</h3>

                        <div class="mt-4 grid gap-2">
                            @forelse ($group['evidences'] as $evidence)
                                @php($url = $this->evidenceUrl($evidence))
                                <div class="flex flex-col gap-2 rounded-lg border border-gray-200 p-3 text-sm dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $this->evidenceName($evidence) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $evidence->description ?? '-' }}</div>
                                    </div>
                                    @if ($url)
                                        <x-filament::button tag="a" size="sm" color="gray" href="{{ $url }}" target="_blank">
                                            Open / Download
                                        </x-filament::button>
                                    @endif
                                </div>
                            @empty
                                <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                    Belum ada bukti capaian.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($activeTab === 'findings')
            <div class="grid gap-4">
                @forelse ($findings as $finding)
                    <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-filament::badge color="gray">{{ $finding->finding_number ?? 'Draf temuan' }}</x-filament::badge>
                                    <x-filament::badge :color="$finding->category?->getColor() ?? 'gray'">{{ $finding->category?->getLabel() ?? '-' }}</x-filament::badge>
                                    <x-filament::badge :color="$finding->status?->getColor() ?? 'gray'">{{ $finding->status?->getLabel() ?? '-' }}</x-filament::badge>
                                </div>
                                <h3 class="mt-3 text-base font-semibold text-gray-950 dark:text-white">
                                    {{ $finding->standardIndicator?->code ?? $finding->checklist?->standardIndicator?->code ?? '-' }}
                                </h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $finding->description }}</p>
                                <div class="mt-4 grid gap-3 text-sm md:grid-cols-2">
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Rekomendasi</div>
                                        <div class="mt-1 text-gray-950 dark:text-white">{{ $finding->recommendation ?: '-' }}</div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Tenggat</div>
                                        <div class="mt-1 text-gray-950 dark:text-white">{{ $finding->due_date?->format('d M Y') ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            @if ($this->canManageFinding($finding))
                                <x-filament::button type="button" size="sm" color="gray" wire:click="openFindingEdit({{ $finding->id }})">
                                    Edit
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-filament::section>
                        <div class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            Belum ada temuan untuk audit ini.
                        </div>
                    </x-filament::section>
                @endforelse
            </div>
        @endif

        @if ($activeTab === 'finalize')
            <x-filament::section>
                <div class="space-y-5">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Checklist belum lengkap</div>
                            <div class="mt-2 text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ $this->incompleteChecklistCount() }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Temuan belum lengkap</div>
                            <div class="mt-2 text-2xl font-semibold text-danger-600 dark:text-danger-400">{{ $this->incompleteFindingCount() }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Total temuan</div>
                            <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $summary['findings'] }}</div>
                        </div>
                    </div>

                    @if ($this->incompleteChecklistCount() > 0)
                        <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 text-sm text-warning-800 dark:border-warning-900 dark:bg-warning-950/30 dark:text-warning-300">
                            Masih ada checklist yang belum diberi assessment.
                        </div>
                    @endif

                    @if ($this->incompleteFindingCount() > 0)
                        <div class="rounded-lg border border-danger-200 bg-danger-50 p-4 text-sm text-danger-800 dark:border-danger-900 dark:bg-danger-950/30 dark:text-danger-300">
                            Ada temuan yang belum memiliki kategori atau deskripsi.
                        </div>
                    @endif

                    @error('finalize')
                        <div class="rounded-lg border border-danger-200 bg-danger-50 p-4 text-sm text-danger-800 dark:border-danger-900 dark:bg-danger-950/30 dark:text-danger-300">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="flex flex-wrap gap-2">
                        @if ($this->canFinalize())
                            <x-filament::button type="button" color="success" wire:click="finalizeAudit">
                                Finalisasi Audit
                            </x-filament::button>
                        @else
                            <x-filament::badge color="gray">
                                Finalisasi hanya untuk lead auditor atau Admin LPM.
                            </x-filament::badge>
                        @endif
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>

    @if ($isAssessmentModalOpen)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/50 px-4 py-6 sm:items-center">
            <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10">
                <form wire:submit="saveAssessment" class="space-y-5 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Assessment Checklist</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Isi hasil assessment dan catatan auditor.</p>
                        </div>
                        <button type="button" wire:click="closeAssessment" class="rounded-lg px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                            Tutup
                        </button>
                    </div>

                    <div>
                        <label for="assessment-result" class="text-sm font-medium text-gray-700 dark:text-gray-200">Assessment result</label>
                        <x-filament::input.wrapper class="mt-2">
                            <x-filament::input.select id="assessment-result" wire:model="assessmentResult">
                                <option value="">Pilih hasil</option>
                                @foreach (\App\Enums\AmiAssessmentResult::cases() as $result)
                                    <option value="{{ $result->value }}">{{ $result->getLabel() }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        @error('assessmentResult')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="auditor-notes" class="text-sm font-medium text-gray-700 dark:text-gray-200">Catatan auditor</label>
                        <x-filament::input.wrapper class="mt-2">
                            <textarea id="auditor-notes" wire:model="auditorNotes" rows="4" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="flex justify-end gap-2">
                        <x-filament::button type="button" color="gray" wire:click="closeAssessment">Batal</x-filament::button>
                        <x-filament::button type="submit">Simpan</x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($isFindingModalOpen)
        <div class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/50 px-4 py-6 sm:items-center">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-xl bg-white shadow-xl ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10">
                <form wire:submit="saveFinding" class="space-y-5 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Temuan Audit</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lengkapi kategori, deskripsi, rekomendasi, dan due date.</p>
                        </div>
                        <button type="button" wire:click="closeFinding" class="rounded-lg px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                            Tutup
                        </button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="finding-category" class="text-sm font-medium text-gray-700 dark:text-gray-200">Kategori</label>
                            <x-filament::input.wrapper class="mt-2">
                                <x-filament::input.select id="finding-category" wire:model="findingCategory">
                                    <option value="">Pilih kategori</option>
                                    @foreach (\App\Enums\AmiFindingCategory::cases() as $category)
                                        <option value="{{ $category->value }}">{{ $category->getLabel() }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                        <div>
                            <label for="finding-status" class="text-sm font-medium text-gray-700 dark:text-gray-200">Status</label>
                            <x-filament::input.wrapper class="mt-2">
                                <x-filament::input.select id="finding-status" wire:model="findingStatus">
                                    @foreach (\App\Enums\AmiFindingStatus::cases() as $status)
                                        <option value="{{ $status->value }}">{{ $status->getLabel() }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                        <div class="md:col-span-2">
                            <label for="finding-description" class="text-sm font-medium text-gray-700 dark:text-gray-200">Deskripsi</label>
                            <x-filament::input.wrapper class="mt-2">
                                <textarea id="finding-description" wire:model="findingDescription" rows="4" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>
                            </x-filament::input.wrapper>
                        </div>
                        <div class="md:col-span-2">
                            <label for="finding-root-cause" class="text-sm font-medium text-gray-700 dark:text-gray-200">Root cause</label>
                            <x-filament::input.wrapper class="mt-2">
                                <textarea id="finding-root-cause" wire:model="findingRootCause" rows="3" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>
                            </x-filament::input.wrapper>
                        </div>
                        <div class="md:col-span-2">
                            <label for="finding-recommendation" class="text-sm font-medium text-gray-700 dark:text-gray-200">Rekomendasi</label>
                            <x-filament::input.wrapper class="mt-2">
                                <textarea id="finding-recommendation" wire:model="findingRecommendation" rows="3" class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"></textarea>
                            </x-filament::input.wrapper>
                        </div>
                        <div>
                            <label for="finding-due-date" class="text-sm font-medium text-gray-700 dark:text-gray-200">Tenggat</label>
                            <x-filament::input.wrapper class="mt-2">
                                <x-filament::input id="finding-due-date" type="date" wire:model="findingDueDate" />
                            </x-filament::input.wrapper>
                        </div>
                    </div>

                    @foreach (['findingCategory', 'findingDescription', 'findingStatus'] as $field)
                        @error($field)
                            <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    @endforeach

                    <div class="flex justify-end gap-2">
                        <x-filament::button type="button" color="gray" wire:click="closeFinding">Batal</x-filament::button>
                        <x-filament::button type="submit">Simpan Temuan</x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-filament-panels::page>
