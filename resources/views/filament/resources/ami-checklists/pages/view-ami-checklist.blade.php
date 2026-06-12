<x-filament-panels::page>
    @php
        $checklist = $this->getRecord();
        $audit = $checklist->audit;
        $indicator = $checklist->standardIndicator;
        $standard = $indicator?->qualityStandard;
        $achievement = $this->unitAchievement();
        $review = $this->latestFinalReview();
        $evidences = $achievement?->evidences ?? collect();
    @endphp

    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $standard?->name ?? 'Standar belum tersedia' }}</p>
                    <h2 class="mt-2 text-xl font-semibold leading-7 text-gray-950 dark:text-white">{{ $indicator?->statement ?? 'Indikator tidak ditemukan' }}</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $audit?->auditeeUnit?->name ?? '-' }} / {{ $audit?->amiPeriod?->name ?? '-' }}</p>
                </div>
                <dl class="grid min-w-full gap-3 text-sm sm:grid-cols-3 lg:min-w-[430px]">
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Target</dt>
                        <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $this->targetSummary() }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Status LPM</dt>
                        <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $achievement?->submission_status?->getLabel() ?? '-' }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Assessment</dt>
                        <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $checklist->assessment_result?->getLabel() ?? 'Belum dinilai' }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_420px]">
            <div class="space-y-6">
                <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Capaian Unit</h3>
                    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Realisasi Angka</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">{{ $achievement?->realization_value ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Status Capaian</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">{{ $achievement?->achievement_status?->getLabel() ?? '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400">Realisasi Naratif</dt>
                            <dd class="mt-1 whitespace-pre-line leading-6 text-gray-700 dark:text-gray-300">{{ $achievement?->realization_text ?: '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400">Catatan Unit</dt>
                            <dd class="mt-1 whitespace-pre-line leading-6 text-gray-700 dark:text-gray-300">{{ $achievement?->notes ?: '-' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Bukti Capaian</h3>
                    <div class="mt-4 space-y-4">
                        @forelse ($evidences as $evidence)
                            @php
                                $url = $this->evidenceUrl($evidence);
                                $previewType = $this->evidencePreviewType($evidence);
                            @endphp
                            <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->evidenceName($evidence) }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $evidence->description ?: 'Tanpa deskripsi' }}</p>
                                    </div>
                                    @if ($url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                            Buka
                                        </a>
                                    @endif
                                </div>

                                @if ($url && $previewType === 'image')
                                    <img src="{{ $url }}" alt="{{ $this->evidenceName($evidence) }}" class="mt-4 max-h-96 w-full rounded-lg object-contain">
                                @elseif ($url && $previewType === 'pdf')
                                    <iframe src="{{ $url }}" class="mt-4 h-96 w-full rounded-lg border border-gray-200 dark:border-gray-800"></iframe>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada bukti capaian.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Validasi LPM</h3>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Hasil Review</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">{{ $review?->status?->getLabel() ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Catatan Review</dt>
                            <dd class="mt-1 whitespace-pre-line leading-6 text-gray-700 dark:text-gray-300">{{ $review?->notes ?: '-' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Assessment Auditor</h3>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Hasil Assessment</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">{{ $checklist->assessment_result?->getLabel() ?? 'Belum dinilai' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Catatan Auditor</dt>
                            <dd class="mt-1 whitespace-pre-line leading-6 text-gray-700 dark:text-gray-300">{{ $checklist->auditor_notes ?: '-' }}</dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
