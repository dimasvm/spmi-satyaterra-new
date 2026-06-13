<x-filament-panels::page>
    <div class="space-y-6">
        <div class="max-w-3xl">
            <h2 class="text-lg font-semibold tracking-tight text-gray-950 dark:text-white">
                Rapat Tinjauan Manajemen
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Evaluasi hasil AMI, capaian standar, dan tindak lanjut untuk peningkatan mutu.
            </p>
        </div>

        @php($stats = $this->stats())

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total RTM</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['total'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Draf</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['draft'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Selesai</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['completed'] }}</div>
            </x-filament::section>
            <x-filament::section>
                <div class="text-sm text-gray-500 dark:text-gray-400">Usulan Menunggu Persetujuan</div>
                <div class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $stats['pending_proposals'] }}</div>
            </x-filament::section>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>
