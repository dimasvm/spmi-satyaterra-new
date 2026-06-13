<x-filament-panels::page>
    @php
        $period = $this->selectedPeriod();
        $stages = $this->stages();
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <x-filament::badge color="primary">
                        Peta Siklus PPEPP
                    </x-filament::badge>

                    <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        Siklus SPMI
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Pantau proses penjaminan mutu dari penetapan standar hingga peningkatan berkelanjutan.
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-filament::badge color="gray">
                            {{ $period['name'] }}
                        </x-filament::badge>
                        <x-filament::badge color="info">
                            Status: {{ $period['status'] }}
                        </x-filament::badge>
                        @if (filled($period['range']))
                            <x-filament::badge color="gray">
                                {{ $period['range'] }}
                            </x-filament::badge>
                        @endif
                    </div>
                </div>

                <div class="w-full max-w-sm">
                    <label for="spmi-period" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        Periode SPMI
                    </label>
                    <x-filament::input.wrapper class="mt-2">
                        <x-filament::input.select id="spmi-period" wire:model.live="selectedSpmiPeriodId">
                            @foreach ($this->periodOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-filament::section>

        <div class="overflow-x-auto pb-2">
            <div class="grid min-w-[860px] grid-cols-5 gap-3">
                @foreach ($stages as $stage)
                    <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-sm font-semibold text-white">
                                {{ $stage['step'] }}
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                    {{ $stage['title'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $stage['progress'] }}% selesai
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-5">
            @forelse ($stages as $stage)
                <x-filament::section>
                    <div class="space-y-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                                    Step {{ $stage['step'] }}
                                </div>
                                <h3 class="mt-1 text-base font-semibold text-gray-950 dark:text-white">
                                    {{ $stage['title'] }}
                                </h3>
                            </div>
                            <x-filament::badge color="gray">
                                {{ $stage['status'] }}
                            </x-filament::badge>
                        </div>

                        <p class="min-h-12 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            {{ $stage['description'] }}
                        </p>

                        <div>
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Progress</span>
                                <span>{{ $stage['progress'] }}%</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                                <div class="h-2 rounded-full bg-primary-600" style="width: {{ min($stage['progress'], 100) }}%"></div>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            @foreach ($stage['metrics'] as $metric)
                                <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 px-3 py-2 dark:border-gray-800">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $metric['label'] }}</span>
                                    <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ is_int($metric['value']) ? number_format($metric['value'], 0, ',', '.') : $metric['value'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <x-filament::button
                            tag="a"
                            :href="$stage['actionUrl']"
                            class="w-full justify-center"
                        >
                            {{ $stage['actionLabel'] }}
                        </x-filament::button>
                    </div>
                </x-filament::section>
            @empty
                <x-filament::section>
                    <div class="py-10 text-center">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Belum ada data siklus
                        </h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Pilih periode SPMI atau buat periode aktif untuk mulai memantau siklus PPEPP.
                        </p>
                    </div>
                </x-filament::section>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
