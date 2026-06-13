<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl space-y-2">
                    <p class="text-sm font-medium text-primary-600 dark:text-primary-400">Pusat Laporan</p>
                    <h1 class="text-2xl font-semibold tracking-normal text-gray-950 dark:text-white">
                        Pusat Laporan
                    </h1>
                    <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">
                        Unduh dan pantau laporan SPMI berdasarkan periode, unit, dan siklus mutu.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-filament::button type="button" color="gray" icon="heroicon-o-magnifying-glass" wire:click="refreshPreview">
                        Preview
                    </x-filament::button>

                    @if (auth()->user()?->can('reports.export'))
                        <x-filament::button type="button" color="danger" icon="heroicon-o-document-arrow-down" wire:click="exportPdf">
                            Generate PDF
                        </x-filament::button>
                        <x-filament::button type="button" color="success" icon="heroicon-o-table-cells" wire:click="exportExcel">
                            Export Excel
                        </x-filament::button>
                    @endif
                </div>
            </div>
        </section>

        <x-filament::section>
            <x-slot name="heading">
                Filter Global
            </x-slot>

            <x-slot name="description">
                Filter ini berlaku untuk kartu laporan dan preview aktif.
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        <section class="space-y-3">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Report Gallery</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pilih jenis laporan tanpa masuk ke tabel mentah.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->reportCards() as $card)
                    <article @class([
                        'flex min-h-72 flex-col rounded-lg border bg-white p-5 shadow-sm transition dark:bg-gray-950',
                        'border-primary-500 ring-2 ring-primary-500/20 dark:border-primary-400' => $card['is_active'],
                        'border-gray-200 hover:border-primary-300 dark:border-gray-800 dark:hover:border-primary-700' => ! $card['is_active'],
                    ])>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <x-filament::icon :icon="$card['icon']" class="h-5 w-5" />
                            </div>

                            @if ($card['is_active'])
                                <span class="rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                    Aktif
                                </span>
                            @endif
                        </div>

                        <div class="mt-4 flex-1 space-y-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $card['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $card['description'] }}</p>
                            </div>

                            <div class="space-y-2">
                                <p class="text-xs font-medium uppercase tracking-normal text-gray-500 dark:text-gray-400">Filter utama</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($card['filters'] as $filter)
                                        <span class="rounded-md border border-gray-200 px-2 py-1 text-xs text-gray-600 dark:border-gray-800 dark:text-gray-300">
                                            {{ $filter }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <x-filament::button type="button" size="sm" color="gray" wire:click="selectReport('{{ $card['type_value'] }}')">
                                Lihat Preview
                            </x-filament::button>

                            @if (auth()->user()?->can('reports.export'))
                                <x-filament::button type="button" size="sm" color="danger" icon="heroicon-o-document-arrow-down" wire:click="exportPdf('{{ $card['type_value'] }}')">
                                    Generate PDF
                                </x-filament::button>
                                <x-filament::button type="button" size="sm" color="success" icon="heroicon-o-table-cells" wire:click="exportExcel('{{ $card['type_value'] }}')">
                                    Export Excel
                                </x-filament::button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <x-filament::section>
            <x-slot name="heading">
                Preview Ringkas
            </x-slot>

            <x-slot name="description">
                {{ $this->activeReportCard()['title'] ?? 'Laporan aktif' }} - 10 data pertama sesuai filter.
            </x-slot>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            @foreach ($headings as $heading)
                                <th scope="col" class="whitespace-nowrap px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">
                                    {{ $heading }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-950">
                        @forelse ($previewRows as $row)
                            <tr>
                                @foreach ($row as $value)
                                    <td class="max-w-sm px-4 py-3 align-top text-gray-700 dark:text-gray-200">
                                        <span class="line-clamp-3">
                                            {{ filled($value) ? $value : '-' }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ max(count($headings), 1) }}" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada data untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
