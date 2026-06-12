<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <x-filament::section>
            <x-slot name="heading">
                Preview Laporan
            </x-slot>

            <x-slot name="description">
                Menampilkan maksimal 15 baris pertama sesuai filter aktif.
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
