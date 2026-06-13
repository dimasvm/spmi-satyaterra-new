@php
    $dashboard = $this->dashboard();
@endphp

<x-filament-widgets::widget>
    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge color="primary">
                            {{ $dashboard['role_label'] }}
                        </x-filament::badge>
                        <x-filament::badge color="gray">
                            Periode: {{ $dashboard['period']['name'] }}
                        </x-filament::badge>
                        <x-filament::badge color="info">
                            Siklus: {{ $dashboard['period']['cycle'] }}
                        </x-filament::badge>
                    </div>

                    <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $dashboard['title'] }}
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        {{ $dashboard['description'] }}
                    </p>
                </div>

                @if (filled($dashboard['shortcuts']))
                    <div class="flex flex-wrap gap-2 xl:max-w-xl xl:justify-end">
                        @foreach ($dashboard['shortcuts'] as $shortcut)
                            <x-filament::button
                                tag="a"
                                :href="$shortcut['url']"
                                size="sm"
                                outlined
                            >
                                {{ $shortcut['label'] }}
                            </x-filament::button>
                        @endforeach
                    </div>
                @endif
            </div>
        </x-filament::section>

        @if (filled($dashboard['stats']))
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($dashboard['stats'] as $stat)
                    <a
                        href="{{ $stat['url'] }}"
                        class="block rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-primary-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-primary-700"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    {{ $stat['label'] }}
                                </p>
                                <p class="mt-3 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                    {{ is_int($stat['value']) ? number_format($stat['value'], 0, ',', '.') : $stat['value'] }}
                                </p>
                            </div>
                            <x-filament::badge :color="$stat['color']">
                                {{ $stat['description'] }}
                            </x-filament::badge>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        @if (filled($dashboard['ppepp'] ?? []))
            <x-filament::section>
                <x-slot name="heading">
                    Progress PPEPP
                </x-slot>

                <x-slot name="description">
                    Ringkasan siklus mutu berdasarkan data periode yang dipilih.
                </x-slot>

                <div class="grid gap-4 lg:grid-cols-5">
                    @foreach ($dashboard['ppepp'] as $card)
                        <a href="{{ $card['url'] }}" class="rounded-lg border border-gray-200 p-4 transition hover:border-primary-300 dark:border-gray-800 dark:hover:border-primary-700">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $card['label'] }}</h3>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $card['description'] }}</p>
                                </div>
                                <x-filament::badge color="gray">
                                    {{ $card['status'] }}
                                </x-filament::badge>
                            </div>

                            <div class="mt-5">
                                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ number_format($card['value'], 0, ',', '.') }} / {{ number_format($card['total'], 0, ',', '.') }}</span>
                                    <span>{{ $card['percentage'] }}%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                                    <div class="h-2 rounded-full bg-primary-600" style="width: {{ min($card['percentage'], 100) }}%"></div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        <div class="grid gap-6 xl:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">
                    Work Queue
                </x-slot>

                <x-slot name="description">
                    Antrean pekerjaan yang perlu diselesaikan atau ditinjau.
                </x-slot>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($dashboard['queues'] as $item)
                        <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-4 py-3">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $item['label'] }}</span>
                            <x-filament::badge :color="$item['color']">
                                {{ number_format($item['count'], 0, ',', '.') }}
                            </x-filament::badge>
                        </a>
                    @empty
                        <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Tidak ada antrean kerja untuk role ini.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">
                    Warning / Attention
                </x-slot>

                <x-slot name="description">
                    Risiko pekerjaan yang terlambat, belum lengkap, atau membutuhkan keputusan.
                </x-slot>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($dashboard['warnings'] as $item)
                        <a href="{{ $item['url'] }}" class="flex items-center justify-between gap-4 py-3">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $item['label'] }}</span>
                            <x-filament::badge :color="$item['color']">
                                {{ number_format($item['count'], 0, ',', '.') }}
                            </x-filament::badge>
                        </a>
                    @empty
                        <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                            Tidak ada perhatian khusus saat ini.
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        @if (filled($dashboard['unit_progress'] ?? []))
            <x-filament::section>
                <x-slot name="heading">
                    Progress Unit
                </x-slot>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Progress Terbaik</h3>
                        <div class="mt-3 space-y-3">
                            @forelse ($dashboard['unit_progress']['best'] as $unit)
                                <div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ $unit['name'] }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ $unit['percentage'] }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                                        <div class="h-2 rounded-full bg-primary-600" style="width: {{ min($unit['percentage'], 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data unit.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-950 dark:text-white">Perlu Pendampingan</h3>
                        <div class="mt-3 space-y-3">
                            @forelse ($dashboard['unit_progress']['lowest'] as $unit)
                                <div>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ $unit['name'] }}</span>
                                        <span class="text-gray-500 dark:text-gray-400">{{ $unit['percentage'] }}%</span>
                                    </div>
                                    <div class="mt-2 h-2 rounded-full bg-gray-100 dark:bg-gray-800">
                                        <div class="h-2 rounded-full bg-primary-600" style="width: {{ min($unit['percentage'], 100) }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data unit.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-widgets::widget>
