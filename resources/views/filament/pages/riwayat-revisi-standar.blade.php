<x-filament-panels::page>
    <div class="space-y-6">
        <div class="max-w-3xl">
            <h2 class="text-lg font-semibold tracking-tight text-gray-950 dark:text-white">
                Riwayat Revisi Standar
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Jejak perubahan standar, indikator, dan target yang sudah diimplementasikan dari usulan peningkatan.
            </p>
        </div>

        @php($stats = $this->stats())

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Riwayat</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['total'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Revisi Standar</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['standard'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Revisi Indikator</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['indicator'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Revisi Target</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['target'] }}</div>
            </x-filament::section>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
