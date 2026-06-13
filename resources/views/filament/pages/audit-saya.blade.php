<x-filament-panels::page>
    @php
        $summary = $this->summary();
        $audits = $this->audits();
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <x-filament::badge color="primary">
                        Auditor Workspace
                    </x-filament::badge>
                    <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        Audit Saya
                    </h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Daftar audit yang ditugaskan kepada Anda, lengkap dengan progress checklist dan temuan aktif.
                    </p>
                </div>

                <x-filament::button tag="a" color="gray" href="{{ $this->correctiveActionUrl() }}">
                    Verifikasi Tindak Lanjut
                </x-filament::button>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total audit</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total'], 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Audit dalam akses Anda</div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Audit aktif</div>
                    <div class="mt-2 text-2xl font-semibold text-info-600 dark:text-info-400">{{ number_format($summary['active'], 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum selesai/final</div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Checklist belum lengkap</div>
                    <div class="mt-2 text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ number_format($summary['unfinished_checklists'], 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perlu assessment</div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Temuan aktif</div>
                    <div class="mt-2 text-2xl font-semibold text-danger-600 dark:text-danger-400">{{ number_format($summary['open_findings'], 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ditutup</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="max-w-xl">
                <label for="audit-search" class="text-sm font-medium text-gray-700 dark:text-gray-200">Cari audit</label>
                <x-filament::input.wrapper class="mt-2" prefix-icon="heroicon-o-magnifying-glass">
                    <x-filament::input
                        id="audit-search"
                        type="search"
                        wire:model.live.debounce.400ms="search"
                        placeholder="Cari periode AMI atau unit auditee"
                    />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        <div class="grid gap-4">
            @forelse ($audits as $audit)
                @php
                    $progress = $this->checklistProgress($audit);
                @endphp

                <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-filament::badge color="gray">
                                    {{ $audit->amiPeriod?->name ?? 'Periode AMI belum tersedia' }}
                                </x-filament::badge>
                                <x-filament::badge :color="$audit->status?->getColor() ?? 'gray'">
                                    {{ $audit->status?->getLabel() ?? '-' }}
                                </x-filament::badge>
                                <x-filament::badge :color="$this->auditorRoleColor($audit)">
                                    {{ $this->auditorRoleLabel($audit) }}
                                </x-filament::badge>
                            </div>

                            <h3 class="mt-3 text-base font-semibold text-gray-950 dark:text-white">
                                {{ $audit->auditeeUnit?->name ?? 'Unit auditee belum tersedia' }}
                            </h3>

                            <div class="mt-4 grid gap-3 text-sm md:grid-cols-4">
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Jadwal audit</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $audit->scheduled_date?->format('d M Y') ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Progres checklist</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $progress['completed'] }}/{{ $progress['total'] }} ({{ $progress['percent'] }}%)</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Jumlah temuan</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ number_format($audit->findings_count ?? 0, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Tim auditor</div>
                                    <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ number_format($audit->auditorAssignments->count(), 0, ',', '.') }} orang</div>
                                </div>
                            </div>

                            <div class="mt-4 h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-2 rounded-full bg-primary-600" style="width: {{ min($progress['percent'], 100) }}%"></div>
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-wrap gap-2 lg:justify-end">
                            <x-filament::button tag="a" size="sm" href="{{ $this->workspaceUrl($audit) }}">
                                Buka Audit
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @empty
                <x-filament::section>
                    <div class="py-10 text-center">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Belum ada audit yang ditugaskan kepada Anda.
                        </h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Audit akan muncul setelah Anda ditambahkan ke tim auditor atau filter pencarian dikosongkan.
                        </p>
                    </div>
                </x-filament::section>
            @endforelse

            <div>
                {{ $audits->links() }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
