<x-filament-panels::page>
    @php
        $review = $this->getRecord();
        $achievement = $review->achievement;
        $assignment = $achievement?->assignment;
        $indicator = $achievement?->standard_indicator;
        $unit = $assignment?->unit;
        $period = $assignment?->spmiPeriod;
        $evidences = $achievement?->evidences ?? collect();
        $reviews = $achievement?->reviews?->sortByDesc('created_at') ?? collect();
        $status = $review->status;

        $reviewBadgeClass = match ($status?->value) {
            'validated' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-800',
            'returned' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-800',
            'rejected' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950 dark:text-red-300 dark:ring-red-800',
            default => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-800',
        };

        $submissionBadgeClass = match ($achievement?->submission_status?->value) {
            'validated' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-800',
            'returned' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950 dark:text-red-300 dark:ring-red-800',
            'submitted' => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-800',
            default => 'bg-gray-100 text-gray-700 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700',
        };
    @endphp

    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold ring-1 {{ $reviewBadgeClass }}">
                            {{ $status?->getLabel() ?? 'Menunggu Review' }}
                        </span>
                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                            {{ $indicator?->code ?? '-' }}
                        </span>
                    </div>

                    <div>
                        <h2 class="text-xl font-semibold leading-7 text-gray-950 dark:text-white">
                            {{ $indicator?->statement ?? 'Indikator tidak ditemukan' }}
                        </h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                            {{ $indicator?->qualityStandard?->name ?? 'Standar belum tersedia' }}
                        </p>
                    </div>
                </div>

                <dl class="grid min-w-full gap-3 text-sm sm:grid-cols-3 lg:min-w-[420px]">
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Unit</dt>
                        <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $unit?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Periode</dt>
                        <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $period?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Target</dt>
                        <dd class="mt-1 font-semibold text-gray-950 dark:text-white">{{ $this->targetSummary() }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-6">
                <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Realisasi Unit</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nilai, narasi, dan status capaian yang dikirim PIC unit.</p>
                        </div>
                        <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold ring-1 {{ $submissionBadgeClass }}">
                            {{ $achievement?->submission_status?->getLabel() ?? '-' }}
                        </span>
                    </div>

                    <dl class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Realisasi</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                                {{ filled($achievement?->realization_value) ? $achievement->realization_value : '-' }}
                                <span class="text-sm font-medium text-gray-500">{{ $indicator?->target_unit }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Status Capaian</dt>
                            <dd class="mt-2">
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                                    {{ $achievement?->achievement_status?->getLabel() ?? '-' }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Dikirim Pada</dt>
                            <dd class="mt-1 font-medium text-gray-950 dark:text-white">{{ $achievement?->submitted_at?->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 grid gap-5 lg:grid-cols-2">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Narasi Realisasi</h4>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $achievement?->realization_text ?: 'Tidak ada narasi realisasi.' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Catatan Unit</h4>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $achievement?->notes ?: 'Tidak ada catatan unit.' }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Bukti Capaian</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Preview PDF dan gambar ditampilkan langsung. Tautan dibuka di tab baru.</p>
                        </div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $evidences->count() }} bukti</span>
                    </div>

                    <div class="mt-6 space-y-5">
                        @forelse ($evidences as $evidence)
                            @php
                                $url = $this->evidenceUrl($evidence);
                                $previewType = $this->evidencePreviewType($evidence);
                                $name = $this->evidenceName($evidence);
                            @endphp

                            <article class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                                <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-950 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-700">
                                                {{ $evidence->file_type?->getLabel() ?? 'File' }}
                                            </span>
                                            <h4 class="truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $name }}</h4>
                                        </div>
                                        @if (filled($evidence->description))
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $evidence->description }}</p>
                                        @endif
                                    </div>

                                    @if ($url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="inline-flex shrink-0 items-center justify-center rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-white dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                                            Buka
                                        </a>
                                    @endif
                                </div>

                                @if ($url && $previewType === 'image')
                                    <div class="bg-gray-100 p-3 dark:bg-gray-950">
                                        <img src="{{ $url }}" alt="{{ $name }}" class="max-h-[560px] w-full rounded-md object-contain">
                                    </div>
                                @elseif ($url && $previewType === 'pdf')
                                    <iframe src="{{ $url }}" title="{{ $name }}" class="h-[620px] w-full bg-gray-100 dark:bg-gray-950"></iframe>
                                @elseif ($url && $previewType === 'file')
                                    <iframe src="{{ $url }}" title="{{ $name }}" class="h-80 w-full bg-gray-100 dark:bg-gray-950"></iframe>
                                    <div class="border-t border-gray-200 px-4 py-3 text-sm text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                        Jika preview tidak tampil di browser, gunakan tombol Buka untuk melihat atau mengunduh file.
                                    </div>
                                @elseif ($url && $previewType === 'link')
                                    <div class="px-4 py-5">
                                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="break-all text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                            {{ $url }}
                                        </a>
                                    </div>
                                @else
                                    <div class="px-4 py-5 text-sm text-gray-500 dark:text-gray-400">
                                        File bukti belum tersedia.
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 px-4 py-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                Belum ada bukti capaian.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Ringkasan Review</h3>
                    <dl class="mt-4 divide-y divide-gray-100 text-sm dark:divide-gray-800">
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-gray-500 dark:text-gray-400">Reviewer</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $review->reviewer?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-gray-500 dark:text-gray-400">Reviewed at</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $review->reviewed_at?->translatedFormat('d M Y, H:i') ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 py-3">
                            <dt class="text-gray-500 dark:text-gray-400">Submitted by</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $achievement?->submittedBy?->name ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5">
                        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">Catatan Review Aktif</h4>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $review->notes ?: 'Belum ada catatan review.' }}</p>
                    </div>
                </section>

                <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Riwayat Review</h3>
                    <div class="mt-5 space-y-4">
                        @forelse ($reviews as $history)
                            @php
                                $historyClass = match ($history->status?->value) {
                                    'validated' => 'bg-emerald-500',
                                    'returned' => 'bg-amber-500',
                                    'rejected' => 'bg-red-500',
                                    default => 'bg-blue-500',
                                };
                            @endphp
                            <div class="flex gap-3">
                                <span class="mt-1.5 h-2.5 w-2.5 rounded-full {{ $historyClass }}"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $history->status?->getLabel() ?? '-' }}</p>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $history->reviewed_at?->translatedFormat('d M Y, H:i') ?? 'Belum direview' }}</span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $history->reviewer?->name ?? 'Reviewer belum ditentukan' }}</p>
                                    @if (filled($history->notes))
                                        <p class="mt-2 whitespace-pre-line text-sm leading-5 text-gray-600 dark:text-gray-400">{{ $history->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada riwayat review.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-filament-panels::page>
