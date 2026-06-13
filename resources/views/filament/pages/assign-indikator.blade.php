<x-filament-panels::page>
    @php
        $steps = $this->steps();
        $selectedStandard = $this->selectedStandard();
        $summary = $this->reviewSummary();
    @endphp

    <form wire:submit="submit" class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <x-filament::badge color="primary">
                        SPMI / Penetapan Standar
                    </x-filament::badge>

                    <h2 class="mt-4 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        Assign Indikator
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Tugaskan indikator mutu ke banyak unit dalam satu alur terarah.
                    </p>
                </div>

                <div class="grid w-full max-w-md grid-cols-2 gap-3">
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Indikator</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">
                            {{ number_format(count($standardIndicatorIds), 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Unit</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">
                            {{ number_format(count($unitIds), 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="overflow-x-auto pb-2">
            <div class="grid min-w-[920px] grid-cols-6 gap-3">
                @foreach ($steps as $step => $meta)
                    <button
                        type="button"
                        wire:click="goToStep({{ $step }})"
                        @class([
                            'rounded-lg border p-3 text-left transition',
                            'border-primary-500 bg-primary-50 dark:border-primary-500/70 dark:bg-primary-950/30' => $currentStep === $step,
                            'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' => $currentStep !== $step,
                        ])
                    >
                        <div class="flex items-center gap-3">
                            <div @class([
                                'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold',
                                'bg-primary-600 text-white' => $currentStep >= $step,
                                'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' => $currentStep < $step,
                            ])>
                                {{ $step }}
                            </div>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-gray-950 dark:text-white">
                                    {{ $meta['label'] }}
                                </div>
                                <div class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $meta['description'] }}
                                </div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <x-filament::section>
            @if ($currentStep === 1)
                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem]">
                    <div>
                        <label for="spmi-period-id" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Periode SPMI
                        </label>
                        <x-filament::input.wrapper class="mt-2">
                            <x-filament::input.select id="spmi-period-id" wire:model.live="spmiPeriodId">
                                <option value="">Pilih periode</option>
                                @foreach ($this->periodOptions() as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        @error('spmiPeriodId')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-950 dark:text-white">
                            Periode aktif
                        </div>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            {{ $this->selectedPeriod()?->name ?? 'Belum ada periode dipilih.' }}
                        </p>
                    </div>
                </div>
            @elseif ($currentStep === 2)
                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_24rem]">
                    <div>
                        <label for="quality-standard-id" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Standar Mutu
                        </label>
                        <x-filament::input.wrapper class="mt-2">
                            <x-filament::input.select id="quality-standard-id" wire:model.live="qualityStandardId">
                                <option value="">Pilih standar</option>
                                @foreach ($this->standardOptions() as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                        @error('qualityStandardId')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <div class="text-sm font-semibold text-gray-950 dark:text-white">
                            Deskripsi standar
                        </div>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                            {{ filled($selectedStandard?->description) ? $selectedStandard->description : 'Pilih standar untuk melihat deskripsi singkat.' }}
                        </p>
                    </div>
                </div>
            @elseif ($currentStep === 3)
                <div class="space-y-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                                Pilih Indikator
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Indikator ditampilkan dari standar yang dipilih.
                            </p>
                        </div>
                        <x-filament::button type="button" color="gray" wire:click="selectAllIndicators">
                            Pilih Semua
                        </x-filament::button>
                    </div>

                    @error('standardIndicatorIds')
                        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror

                    <div class="grid gap-3 lg:grid-cols-2">
                        @forelse ($this->availableIndicators() as $indicator)
                            <label class="flex cursor-pointer gap-3 rounded-lg border border-gray-200 p-4 transition hover:border-primary-400 dark:border-gray-800 dark:hover:border-primary-500">
                                <input
                                    type="checkbox"
                                    value="{{ $indicator->id }}"
                                    wire:model.live="standardIndicatorIds"
                                    class="mt-1 rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900"
                                >
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-gray-950 dark:text-white">
                                        {{ $indicator->code }} · {{ $indicator->statement }}
                                    </span>
                                    <span class="mt-2 inline-flex rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                        Target {{ $indicator->target_operator?->getLabel() }} {{ (float) $indicator->target_value }} {{ $indicator->target_unit }}
                                    </span>
                                </span>
                            </label>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                Belum ada indikator pada standar ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            @elseif ($currentStep === 4)
                <div class="space-y-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div class="w-full max-w-sm">
                            <label for="unit-type" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                Filter Jenis Unit
                            </label>
                            <x-filament::input.wrapper class="mt-2">
                                <x-filament::input.select id="unit-type" wire:model.live="unitType">
                                    <option value="">Semua jenis unit</option>
                                    @foreach ($this->unitTypeOptions() as $type => $label)
                                        <option value="{{ $type }}">{{ $label }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <x-filament::button type="button" color="gray" wire:click="selectAllStudyPrograms">
                                Pilih Semua Prodi
                            </x-filament::button>
                            <x-filament::button type="button" color="gray" wire:click="selectAllUnits">
                                Pilih Semua Unit
                            </x-filament::button>
                        </div>
                    </div>

                    @error('unitIds')
                        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @forelse ($this->availableUnits() as $unit)
                            <label class="flex cursor-pointer gap-3 rounded-lg border border-gray-200 p-4 transition hover:border-primary-400 dark:border-gray-800 dark:hover:border-primary-500">
                                <input
                                    type="checkbox"
                                    value="{{ $unit->id }}"
                                    wire:model.live="unitIds"
                                    class="mt-1 rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900"
                                >
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-gray-950 dark:text-white">
                                        {{ $unit->name }}
                                    </span>
                                    <span class="mt-2 inline-flex rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                        {{ $unit->type?->getLabel() ?? 'Unit' }}
                                    </span>
                                </span>
                            </label>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                Tidak ada unit aktif untuk filter ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            @elseif ($currentStep === 5)
                <div class="grid gap-5 lg:grid-cols-3">
                    <div>
                        <label for="due-date" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Deadline
                        </label>
                        <x-filament::input.wrapper class="mt-2">
                            <x-filament::input id="due-date" type="date" wire:model="dueDate" />
                        </x-filament::input.wrapper>
                        @error('dueDate')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="assignment-status" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Status Awal
                        </label>
                        <x-filament::input.wrapper class="mt-2">
                            <x-filament::input.select id="assignment-status" wire:model="status">
                                <option value="{{ \App\Enums\IndicatorAssignmentStatus::Assigned->value }}">
                                    {{ \App\Enums\IndicatorAssignmentStatus::Assigned->getLabel() }}
                                </option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div>
                        <label for="priority" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Prioritas
                        </label>
                        <x-filament::input.wrapper class="mt-2">
                            <x-filament::input.select id="priority" wire:model="priority">
                                @foreach (\App\Enums\IndicatorAssignmentPriority::cases() as $priorityOption)
                                    <option value="{{ $priorityOption->value }}">{{ $priorityOption->getLabel() }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="notes" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                            Catatan
                        </label>
                        <x-filament::input.wrapper class="mt-2">
                            <textarea
                                id="notes"
                                wire:model="notes"
                                rows="3"
                                class="block w-full border-none bg-transparent px-3 py-2 text-sm text-gray-950 outline-none placeholder:text-gray-400 focus:ring-0 dark:text-white"
                                placeholder="Opsional"
                            ></textarea>
                        </x-filament::input.wrapper>
                        @error('notes')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @else
                <div class="space-y-5">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">
                            Review Assignment
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Periksa ringkasan sebelum membuat atau memperbarui penugasan.
                        </p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Periode</div>
                            <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $summary['period'] ?? '-' }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Standar</div>
                            <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $summary['standard'] ?? '-' }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Deadline</div>
                            <div class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">{{ $summary['due_date'] ?? 'Tidak diatur' }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <div class="text-xs text-gray-500 dark:text-gray-400">Total Assignment</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['total_assignments'], 0, ',', '.') }}</div>
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-3">
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Jumlah indikator</div>
                            <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['indicator_count'], 0, ',', '.') }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Jumlah unit</div>
                            <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['unit_count'], 0, ',', '.') }}</div>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Sudah ada dan akan diperbarui</div>
                            <div class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ number_format($summary['existing_assignments'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <x-filament::button
                type="button"
                color="gray"
                wire:click="previousStep"
                :disabled="$currentStep === 1"
            >
                Kembali
            </x-filament::button>

            @if ($currentStep < count($steps))
                <x-filament::button type="button" wire:click="nextStep">
                    Lanjut
                </x-filament::button>
            @else
                <x-filament::button type="submit">
                    Simpan Assignment
                </x-filament::button>
            @endif
        </div>
    </form>
</x-filament-panels::page>
