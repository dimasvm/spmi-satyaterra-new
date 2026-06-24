@php
    $record = $this->record();
    $stats = $this->stats();
    $tabs = $this->tabs();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-4xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge color="primary">{{ $record->code }}</x-filament::badge>
                        <x-filament::badge :color="$record->status?->getColor() ?? 'gray'">
                            {{ $record->status?->getLabel() ?? '-' }}
                        </x-filament::badge>
                        <x-filament::badge color="gray">Versi {{ $record->version ?? 1 }}</x-filament::badge>
                    </div>

                    <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $record->name }}
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        {{ filled($record->description) ? $record->description : 'Belum ada deskripsi standar.' }}
                    </p>
                </div>

                <div class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-2xl xl:grid-cols-5">
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Pernyataan</div>
                        <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($stats['statements'], 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Indikator</div>
                        <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($stats['indicators'], 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Dokumen</div>
                        <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($stats['documents'], 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Unit</div>
                        <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($stats['assignments'], 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Capaian</div>
                        <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($stats['achievements'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="overflow-x-auto">
            <div class="flex min-w-max gap-2 rounded-lg border border-gray-200 bg-white p-2 dark:border-gray-800 dark:bg-gray-900">
                @foreach ($tabs as $tab)
                    <button
                        type="button"
                        wire:click="$set('activeTab', '{{ $tab['key'] }}')"
                        @class([
                            'rounded-md px-3 py-2 text-sm font-medium transition',
                            'bg-primary-600 text-white' => $this->activeTab === $tab['key'],
                            'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800' => $this->activeTab !== $tab['key'],
                        ])
                    >
                        {{ $tab['label'] }}
                        @if ($tab['count'] !== null)
                            <span @class([
                                'ml-2 rounded-full px-2 py-0.5 text-xs',
                                'bg-white/20 text-white' => $this->activeTab === $tab['key'],
                                'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' => $this->activeTab !== $tab['key'],
                            ])>
                                {{ number_format($tab['count'], 0, ',', '.') }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        @if ($this->activeTab === 'summary')
            <x-filament::section>
                <x-slot name="heading">Ringkasan Standar</x-slot>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Kode standar</div>
                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $record->code }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Kategori</div>
                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $record->category?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Periode</div>
                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $record->spmiPeriod?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Approved by</div>
                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $record->approver?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Approved at</div>
                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $record->approved_at?->format('d M Y H:i') ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                        <div class="mt-1">
                            <x-filament::badge :color="$record->status?->getColor() ?? 'gray'">
                                {{ $record->status?->getLabel() ?? '-' }}
                            </x-filament::badge>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Versi</div>
                        <div class="mt-1 font-medium text-gray-950 dark:text-white">{{ $record->version ?? 1 }}</div>
                    </div>
                    <div class="md:col-span-2 xl:col-span-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Deskripsi</div>
                        <div class="mt-1 text-sm leading-6 text-gray-800 dark:text-gray-200">
                            {{ filled($record->description) ? $record->description : 'Belum ada deskripsi.' }}
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @elseif ($this->activeTab === 'statements')
            <x-filament::section>
                <x-slot name="heading">Pernyataan Standar</x-slot>

                <div class="grid gap-4 lg:grid-cols-2">
                    @forelse ($this->statements() as $statement)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <x-filament::badge color="gray">{{ $statement->code }}</x-filament::badge>
                                    <h3 class="mt-3 text-sm font-semibold leading-6 text-gray-950 dark:text-white">{{ $statement->statement }}</h3>
                                </div>
                                <x-filament::badge color="primary">
                                    {{ number_format($statement->indicators_count, 0, ',', '.') }} indikator
                                </x-filament::badge>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400 lg:col-span-2">
                            Belum ada pernyataan standar.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        @elseif ($this->activeTab === 'indicators')
            <x-filament::section>
                <x-slot name="heading">Indikator Standar</x-slot>

                <div class="grid gap-4 lg:grid-cols-2">
                    @forelse ($this->indicators() as $indicator)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <x-filament::badge color="gray">{{ $indicator->code }}</x-filament::badge>
                                    @if ($indicator->standardStatement)
                                        <x-filament::badge color="primary">{{ $indicator->standardStatement->code }}</x-filament::badge>
                                    @endif
                                    <h3 class="mt-3 text-sm font-semibold text-gray-950 dark:text-white">{{ $indicator->statement }}</h3>
                                </div>
                                <x-filament::button tag="a" :href="$this->indicatorEditUrl($indicator)" size="sm" color="gray" outlined>
                                    Edit
                                </x-filament::button>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Target: <span class="font-medium text-gray-950 dark:text-white">{{ $this->targetLabel($indicator) }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Jenis: <span class="font-medium text-gray-950 dark:text-white">{{ $indicator->indicator_type?->getLabel() ?? '-' }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Wajib bukti: <span class="font-medium text-gray-950 dark:text-white">{{ $indicator->evidence_required ? 'Ya' : 'Tidak' }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Unit ditugaskan: <span class="font-medium text-gray-950 dark:text-white">{{ number_format($indicator->assignments_count, 0, ',', '.') }}</span></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400 lg:col-span-2">
                            Belum ada indikator untuk standar ini.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        @elseif ($this->activeTab === 'documents')
            <x-filament::section>
                <x-slot name="heading">Dokumen Terkait</x-slot>
                <x-slot name="headerEnd">
                    <x-filament::button tag="a" :href="$this->documentCreateUrl()" size="sm">
                        Upload Dokumen
                    </x-filament::button>
                </x-slot>

                <div class="grid gap-4 lg:grid-cols-2">
                    @forelse ($this->documents() as $document)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $document->title }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $document->document_number ?: 'Nomor dokumen belum diisi' }}</p>
                                </div>
                                <x-filament::badge :color="$this->documentStatusColor($document)">
                                    {{ $document->status?->getLabel() ?? '-' }}
                                </x-filament::badge>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Jenis: <span class="font-medium text-gray-950 dark:text-white">{{ $document->document_type?->getLabel() ?? '-' }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Versi: <span class="font-medium text-gray-950 dark:text-white">{{ $document->version ?? '-' }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Approved: <span class="font-medium text-gray-950 dark:text-white">{{ $document->approved_at?->format('d M Y') ?? '-' }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Uploader: <span class="font-medium text-gray-950 dark:text-white">{{ $document->uploadedBy?->name ?? '-' }}</span></div>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($this->documentUrl($document))
                                    <x-filament::button tag="a" :href="$this->documentUrl($document)" size="sm" color="gray" outlined target="_blank">
                                        Buka
                                    </x-filament::button>
                                @endif
                                @if (auth()->user()?->can('approve', $document) && $document->status !== \App\Enums\QualityDocumentStatus::Active)
                                    <x-filament::button wire:click="approveDocument({{ $document->id }})" size="sm" color="success" outlined>
                                        Approve
                                    </x-filament::button>
                                @endif
                                @if (auth()->user()?->can('archive', $document) && $document->status !== \App\Enums\QualityDocumentStatus::Archived)
                                    <x-filament::button wire:click="archiveDocument({{ $document->id }})" size="sm" color="warning" outlined>
                                        Archive
                                    </x-filament::button>
                                @endif
                                <x-filament::button tag="a" :href="$this->documentEditUrl($document)" size="sm" color="gray" outlined>
                                    Kelola
                                </x-filament::button>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400 lg:col-span-2">
                            Belum ada dokumen mutu yang terhubung ke standar ini.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        @elseif ($this->activeTab === 'assignments')
            <x-filament::section>
                <x-slot name="heading">Unit Ditugaskan</x-slot>

                <div class="grid gap-4 lg:grid-cols-2">
                    @forelse ($this->assignments() as $assignment)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $assignment->unit?->name ?? '-' }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $assignment->standardIndicator?->code }} - {{ $assignment->standardIndicator?->statement }}</p>
                                </div>
                                <x-filament::badge :color="$this->assignmentStatusColor($assignment)">
                                    {{ $assignment->status?->getLabel() ?? '-' }}
                                </x-filament::badge>
                            </div>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Deadline: <span class="font-medium text-gray-950 dark:text-white">{{ $assignment->due_date?->format('d M Y') ?? '-' }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Periode: <span class="font-medium text-gray-950 dark:text-white">{{ $assignment->spmiPeriod?->name ?? '-' }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Capaian: <span class="font-medium text-gray-950 dark:text-white">{{ number_format($assignment->achievements_count, 0, ',', '.') }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Prioritas: <span class="font-medium text-gray-950 dark:text-white">{{ $assignment->priority?->getLabel() ?? '-' }}</span></div>
                            </div>
                            <div class="mt-4">
                                <x-filament::button tag="a" :href="$this->assignmentEditUrl($assignment)" size="sm" color="gray" outlined>
                                    Edit Deadline / Assignment
                                </x-filament::button>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400 lg:col-span-2">
                            Belum ada unit yang ditugaskan untuk standar ini.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        @elseif ($this->activeTab === 'achievements')
            <x-filament::section>
                <x-slot name="heading">Capaian</x-slot>

                <div class="grid gap-4">
                    @forelse ($this->achievements() as $achievement)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $achievement->assignment?->unit?->name ?? '-' }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $achievement->assignment?->standardIndicator?->code }} - {{ $achievement->assignment?->standardIndicator?->statement }}</p>
                                </div>
                                <x-filament::button tag="a" :href="$this->achievementViewUrl($achievement)" size="sm" color="gray" outlined>
                                    Lihat Detail
                                </x-filament::button>
                            </div>
                            <div class="mt-4 grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Target: <span class="font-medium text-gray-950 dark:text-white">{{ $achievement->assignment?->standardIndicator ? $this->targetLabel($achievement->assignment->standardIndicator) : '-' }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Realisasi: <span class="font-medium text-gray-950 dark:text-white">{{ $achievement->realization_value ?? $achievement->realization_text ?? '-' }}</span></div>
                                <div><x-filament::badge :color="$achievement->achievement_status?->getColor() ?? 'gray'">{{ $achievement->achievement_status?->getLabel() ?? 'Belum dinilai' }}</x-filament::badge></div>
                                <div><x-filament::badge :color="$achievement->submission_status?->getColor() ?? 'gray'">{{ $achievement->submission_status?->getLabel() ?? '-' }}</x-filament::badge></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Bukti: <span class="font-medium text-gray-950 dark:text-white">{{ number_format($achievement->evidences_count, 0, ',', '.') }}</span></div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Review: <span class="font-medium text-gray-950 dark:text-white">{{ $achievement->latestReview?->status?->getLabel() ?? '-' }}</span></div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Belum ada capaian untuk standar ini.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        @elseif ($this->activeTab === 'revisions')
            <x-filament::section>
                <x-slot name="heading">Riwayat Revisi</x-slot>
                <x-slot name="headerEnd">
                    <x-filament::button tag="a" :href="$this->proposalWorkspaceUrl()" size="sm">
                        Buat Usulan Revisi
                    </x-filament::button>
                </x-slot>

                <div class="space-y-3">
                    @forelse ($this->revisionItems() as $item)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <x-filament::badge color="gray">{{ $item['type'] }}</x-filament::badge>
                                    <h3 class="mt-3 text-sm font-semibold text-gray-950 dark:text-white">{{ $item['title'] }}</h3>
                                    <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $item['description'] }}</p>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 sm:text-right">
                                    <div>{{ $item['status'] }}</div>
                                    <div>{{ $item['date'] }}</div>
                                    <div>{{ $item['actor'] }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                            Belum ada riwayat revisi atau usulan peningkatan untuk standar ini.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
