<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $record->title }}
                    </h2>
                    <x-filament::badge :color="$record->proposal_type?->getColor()">
                        {{ $record->proposal_type?->getLabel() }}
                    </x-filament::badge>
                    <x-filament::badge :color="$record->status?->getColor()">
                        {{ $record->status?->getLabel() }}
                    </x-filament::badge>
                </div>
                <div class="grid gap-2 text-sm text-gray-600 dark:text-gray-400 sm:grid-cols-2 xl:grid-cols-4">
                    <div>RTM Asal: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->managementReview?->title ?? '-' }}</span></div>
                    <div>Standar: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->qualityStandard?->name ?? '-' }}</span></div>
                    <div>Indikator: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->standardIndicator?->code ?? '-' }}</span></div>
                    <div>Periode Tujuan: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->targetSpmiPeriod?->name ?? '-' }}</span></div>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-6 xl:grid-cols-3">
            <x-filament::section class="xl:col-span-2">
                <x-slot name="heading">Informasi Usulan</x-slot>

                <div class="space-y-5 text-sm">
                    <div>
                        <div class="font-medium text-gray-950 dark:text-white">Latar Belakang</div>
                        <p class="mt-1 whitespace-pre-line text-gray-600 dark:text-gray-400">{{ $record->background ?: '-' }}</p>
                    </div>
                    <div>
                        <div class="font-medium text-gray-950 dark:text-white">Kondisi Saat Ini</div>
                        <p class="mt-1 whitespace-pre-line text-gray-600 dark:text-gray-400">{{ $record->current_condition ?: '-' }}</p>
                    </div>
                    <div>
                        <div class="font-medium text-gray-950 dark:text-white">Usulan Perubahan</div>
                        <p class="mt-1 whitespace-pre-line text-gray-600 dark:text-gray-400">{{ $record->proposed_change }}</p>
                    </div>
                    <div>
                        <div class="font-medium text-gray-950 dark:text-white">Alasan</div>
                        <p class="mt-1 whitespace-pre-line text-gray-600 dark:text-gray-400">{{ $record->reason ?: '-' }}</p>
                    </div>
                    <div>
                        <div class="font-medium text-gray-950 dark:text-white">Dampak yang Diharapkan</div>
                        <p class="mt-1 whitespace-pre-line text-gray-600 dark:text-gray-400">{{ $record->expected_impact ?: '-' }}</p>
                    </div>
                </div>
            </x-filament::section>

            <div class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">Objek Terkait</x-slot>

                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <div>Standar: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->qualityStandard?->name ?? '-' }}</span></div>
                        <div>Indikator: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->standardIndicator?->code ?? '-' }}</span></div>
                        <div>Periode Tujuan: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->targetSpmiPeriod?->name ?? '-' }}</span></div>
                        <div>RTM Asal: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->managementReview?->title ?? '-' }}</span></div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Review</x-slot>

                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <div>Reviewer: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->reviewedBy?->name ?? '-' }}</span></div>
                        <div>Waktu: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->reviewed_at?->translatedFormat('d M Y H:i') ?? '-' }}</span></div>
                        <div>
                            <div class="font-medium text-gray-950 dark:text-white">Catatan</div>
                            <p class="mt-1 whitespace-pre-line">{{ $record->review_notes ?: '-' }}</p>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">Implementasi</x-slot>

                    <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <div>Pelaksana: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->implementedBy?->name ?? '-' }}</span></div>
                        <div>Waktu: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->implemented_at?->translatedFormat('d M Y H:i') ?? '-' }}</span></div>
                        <div>Standar Baru: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->createdStandard?->name ?? '-' }}</span></div>
                        <div>Indikator Baru: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->createdIndicator?->code ?? '-' }}</span></div>
                    </div>
                </x-filament::section>
            </div>
        </div>

        <x-filament::section>
            <x-slot name="heading">Preview Perubahan</x-slot>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-sm font-medium text-gray-950 dark:text-white">Data Saat Ini</div>
                    <dl class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div><dt class="font-medium text-gray-800 dark:text-gray-200">Standar</dt><dd>{{ $record->qualityStandard?->description ?: '-' }}</dd></div>
                        <div><dt class="font-medium text-gray-800 dark:text-gray-200">Indikator</dt><dd>{{ $record->standardIndicator?->statement ?: '-' }}</dd></div>
                        <div><dt class="font-medium text-gray-800 dark:text-gray-200">Target</dt><dd>{{ $record->standardIndicator?->target_operator?->value ?? $record->standardIndicator?->target_operator }} {{ $record->standardIndicator?->target_value ?? '-' }} {{ $record->standardIndicator?->target_unit }}</dd></div>
                    </dl>
                </div>
                <div class="rounded-lg border border-primary-200 bg-primary-50/40 p-4 dark:border-primary-800 dark:bg-primary-950/20">
                    <div class="text-sm font-medium text-gray-950 dark:text-white">Usulan</div>
                    <dl class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                        <div><dt class="font-medium text-gray-900 dark:text-gray-100">Nama Standar</dt><dd>{{ $record->proposed_standard_name ?: '-' }}</dd></div>
                        <div><dt class="font-medium text-gray-900 dark:text-gray-100">Deskripsi Standar</dt><dd>{{ $record->proposed_standard_description ?: $record->proposed_change }}</dd></div>
                        <div><dt class="font-medium text-gray-900 dark:text-gray-100">Rumusan Indikator</dt><dd>{{ $record->proposed_indicator_statement ?: '-' }}</dd></div>
                        <div><dt class="font-medium text-gray-900 dark:text-gray-100">Target Baru</dt><dd>{{ $record->proposed_target_operator ?: '-' }} {{ $record->proposed_target_value ?? '-' }} {{ $record->proposed_target_unit }}</dd></div>
                    </dl>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Riwayat Revisi Standar</x-slot>

            <div class="space-y-3">
                @forelse ($record->revisionHistories as $history)
                    <div class="rounded-lg border border-gray-200 bg-white p-4 text-sm dark:border-gray-800 dark:bg-gray-950">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-filament::badge :color="$history->revision_type?->getColor()">
                                    {{ $history->revision_type?->getLabel() }}
                                </x-filament::badge>
                                <span class="text-gray-500 dark:text-gray-400">{{ $history->revised_at?->translatedFormat('d M Y H:i') ?? '-' }}</span>
                            </div>
                            <span class="text-gray-600 dark:text-gray-300">{{ $history->revisedBy?->name ?? '-' }}</span>
                        </div>
                        @if (filled($history->notes))
                            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $history->notes }}</p>
                        @endif
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada riwayat revisi dari usulan ini.
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
