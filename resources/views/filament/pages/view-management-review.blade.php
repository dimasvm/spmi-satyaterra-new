<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-semibold tracking-tight text-gray-950 dark:text-white">
                            {{ $record->title }}
                        </h2>
                        <x-filament::badge :color="$record->status?->getColor()">
                            {{ $record->status?->getLabel() }}
                        </x-filament::badge>
                    </div>
                    <div class="grid gap-2 text-sm text-gray-600 dark:text-gray-400 sm:grid-cols-2 xl:grid-cols-4">
                        <div>Periode SPMI: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->spmiPeriod?->name ?? '-' }}</span></div>
                        <div>Periode AMI: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->amiPeriod?->name ?? '-' }}</span></div>
                        <div>Tanggal: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->meeting_date?->translatedFormat('d M Y') ?? '-' }}</span></div>
                        <div>Lokasi: <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record->location ?: '-' }}</span></div>
                    </div>
                    @if (filled($record->agenda))
                        <p class="max-w-4xl text-sm text-gray-600 dark:text-gray-400">{{ $record->agenda }}</p>
                    @endif
                </div>
            </div>
        </x-filament::section>

        @php($stats = $this->stats())

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Item</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['items'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Temuan Mayor</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['major'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Temuan Minor</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['minor'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">TL Belum Selesai</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['open_actions'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Usulan</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['proposals'] }}</div>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">Ringkasan Hasil AMI</x-slot>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Temuan mayor</div>
                    <div class="mt-2 text-2xl font-semibold text-danger-600 dark:text-danger-400">{{ $stats['major'] }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Temuan minor</div>
                    <div class="mt-2 text-2xl font-semibold text-warning-600 dark:text-warning-400">{{ $stats['minor'] }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Tindak lanjut terbuka</div>
                    <div class="mt-2 text-2xl font-semibold text-info-600 dark:text-info-400">{{ $stats['open_actions'] }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Usulan peningkatan</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['proposals'] }}</div>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Peserta</x-slot>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Jabatan</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Unit</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Kehadiran</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-950">
                        @forelse ($record->participants as $participant)
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">{{ $participant->user?->name ?: $participant->name ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $participant->position ?: '-' }}</td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $participant->unit?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <x-filament::badge :color="$participant->attendance_status?->getColor()">
                                        {{ $participant->attendance_status?->getLabel() }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if (auth()->user()?->hasAnyRole(['super_admin', 'admin_lpm']))
                                        <x-filament::button size="sm" color="gray" wire:click="deleteParticipant({{ $participant->id }})">
                                            Hapus
                                        </x-filament::button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada peserta.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Item Pembahasan</x-slot>

            <div class="space-y-3">
                @forelse ($record->items as $item)
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-medium text-gray-950 dark:text-white">{{ $item->title }}</h3>
                                    <x-filament::badge :color="$item->item_type?->getColor()">{{ $item->item_type?->getLabel() }}</x-filament::badge>
                                    <x-filament::badge :color="$item->priority?->getColor()">{{ $item->priority?->getLabel() }}</x-filament::badge>
                                    <x-filament::badge :color="$item->status?->getColor()">{{ $item->status?->getLabel() }}</x-filament::badge>
                                </div>
                                @if (filled($item->description))
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ str($item->description)->limit(180) }}</p>
                                @endif
                                @if (filled($item->decision))
                                    <p class="text-sm text-gray-700 dark:text-gray-300"><span class="font-medium">Keputusan:</span> {{ $item->decision }}</p>
                                @endif
                                @if (filled($item->recommendation))
                                    <p class="text-sm text-gray-700 dark:text-gray-300"><span class="font-medium">Rekomendasi:</span> {{ $item->recommendation }}</p>
                                @endif
                            </div>

                            @if (auth()->user()?->hasAnyRole(['super_admin', 'admin_lpm']))
                                <div class="flex shrink-0 flex-wrap gap-2">
                                    <x-filament::button size="sm" color="gray" wire:click="markItemDiscussed({{ $item->id }})">
                                        Tandai Dibahas
                                    </x-filament::button>
                                    <x-filament::button size="sm" wire:click="createProposalFromItem({{ $item->id }})">
                                        Buat Usulan
                                    </x-filament::button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada item pembahasan.
                    </div>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Keputusan RTM</x-slot>

            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <div class="text-sm font-medium text-gray-950 dark:text-white">Ringkasan</div>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-600 dark:text-gray-400">{{ $record->summary ?: '-' }}</p>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-950 dark:text-white">Kesimpulan</div>
                    <p class="mt-2 whitespace-pre-line text-sm text-gray-600 dark:text-gray-400">{{ $record->conclusion ?: '-' }}</p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Usulan Peningkatan</x-slot>

            <div class="space-y-3">
                @forelse ($record->improvementProposals as $proposal)
                    <a href="{{ \App\Filament\Pages\ViewStandardImprovementProposal::getUrl(['proposal' => $proposal]) }}" class="block rounded-lg border border-gray-200 bg-white p-4 transition hover:border-primary-500 dark:border-gray-800 dark:bg-gray-950">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="font-medium text-gray-950 dark:text-white">{{ $proposal->title }}</div>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $proposal->qualityStandard?->name ?? 'Tanpa standar' }} · {{ $proposal->standardIndicator?->code ?? 'Tanpa indikator' }}
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <x-filament::badge :color="$proposal->proposal_type?->getColor()">{{ $proposal->proposal_type?->getLabel() }}</x-filament::badge>
                                <x-filament::badge :color="$proposal->status?->getColor()">{{ $proposal->status?->getLabel() }}</x-filament::badge>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        Belum ada usulan peningkatan dari RTM ini.
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
