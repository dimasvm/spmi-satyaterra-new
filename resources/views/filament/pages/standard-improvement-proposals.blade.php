<x-filament-panels::page>
    <div class="space-y-6">
        <div class="max-w-3xl">
            <h2 class="text-lg font-semibold tracking-tight text-gray-950 dark:text-white">
                Usulan Peningkatan Standar
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Usulan revisi standar, indikator, dan target berdasarkan hasil evaluasi mutu.
            </p>
        </div>

        @php($stats = $this->stats())

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Draft</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['draft'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Diajukan</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['submitted'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Disetujui</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['approved'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Ditolak</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['rejected'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Diimplementasikan</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['implemented'] }}</div>
            </x-filament::section>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
