<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">Matriks Penugasan Indikator</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lihat distribusi indikator per unit secara menyeluruh.</p>
            </div>
            <a
                href="{{ \App\Filament\Resources\IndicatorUnitAssignments\IndicatorUnitAssignmentResource::getUrl('index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
            >
                Kembali
            </a>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1fr_360px]">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                    <select wire:model.live="period" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                        @foreach ($this->periods as $periodOption)
                            <option value="{{ $periodOption->id }}">{{ $periodOption->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="standard" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="">Semua standar</option>
                        @foreach ($this->standards as $standardOption)
                            <option value="{{ $standardOption->id }}">{{ $standardOption->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                        <option value="all">Semua status</option>
                        <option value="unassigned">Belum ditugaskan</option>
                        <option value="assigned">Ditugaskan</option>
                        <option value="completed">Selesai</option>
                    </select>
                    <input wire:model.live.debounce.400ms="search" type="search" placeholder="Cari indikator..." class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500">Cakupan Unit</p>
                    <p class="mt-2 text-2xl font-semibold text-green-600">{{ $this->coverageStats['covered'] }}/{{ $this->coverageStats['units'] }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500">Indikator Tanpa PIC</p>
                    <p class="mt-2 text-2xl font-semibold text-amber-600">{{ $this->coverageStats['without_pic'] }}</p>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                        <tr>
                            <th class="sticky left-0 z-10 min-w-64 bg-gray-50 px-4 py-3 dark:bg-gray-950">Indikator</th>
                            @foreach ($this->units as $unit)
                                <th class="px-4 py-3 text-center">{{ $unit->code }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($this->indicators as $indicator)
                            <tr>
                                <td class="sticky left-0 z-10 bg-white px-4 py-4 dark:bg-gray-900">
                                    <div class="font-semibold text-primary-600">{{ $indicator->code }}</div>
                                    <div class="mt-1 text-sm text-gray-700 dark:text-gray-200">{{ $indicator->statement }}</div>
                                </td>
                                @foreach ($this->units as $unit)
                                    @php($assignment = $this->assignmentFor($indicator, $unit))
                                    <td class="px-4 py-4 text-center">
                                        @if ($assignment?->is_primary_pic)
                                            <span title="PIC Utama" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-xs font-bold text-green-700 ring-1 ring-green-300 dark:bg-green-950 dark:text-green-300">PIC</span>
                                        @elseif ($assignment)
                                            <span title="Unit Terlibat" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-xs font-bold text-green-700 ring-1 ring-green-200 dark:bg-green-950 dark:text-green-300">OK</span>
                                        @else
                                            <span title="Tidak Ditugaskan" class="inline-flex h-10 w-10 rounded-xl bg-gray-50 ring-1 ring-gray-200 dark:bg-gray-950 dark:ring-gray-800"></span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 border-t border-gray-200 p-4 text-sm dark:border-gray-800 md:grid-cols-3">
                <div><span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-lg bg-green-50 text-xs text-green-700">OK</span> Unit terlibat</div>
                <div><span class="mr-2 inline-flex h-6 w-6 items-center justify-center rounded-lg bg-green-100 text-xs text-green-700">PIC</span> PIC utama</div>
                <div><span class="mr-2 inline-flex h-6 w-6 rounded-lg bg-gray-50 ring-1 ring-gray-200"></span> Tidak ditugaskan</div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
