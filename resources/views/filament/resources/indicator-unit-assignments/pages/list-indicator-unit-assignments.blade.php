<x-filament-panels::page>
    <div class="space-y-6">

        {{-- <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @php
                $cards = [
                    ['label' => 'Total Indikator', 'value' => $this->stats['total'], 'hint' => 'Semua indikator terdaftar', 'color' => 'blue'],
                    ['label' => 'Belum Ditugaskan', 'value' => $this->stats['unassigned'], 'hint' => 'Perlu penugasan unit', 'color' => 'amber'],
                    ['label' => 'Sedang Berjalan', 'value' => $this->stats['in_progress'], 'hint' => 'Dalam proses pelaksanaan', 'color' => 'blue'],
                    ['label' => 'Selesai', 'value' => $this->stats['completed'], 'hint' => 'Telah diselesaikan', 'color' => 'green'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-4">
                        <div @class([
                            'flex h-14 w-14 items-center justify-center rounded-xl text-xl font-semibold',
                            'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300' => $card['color'] === 'blue',
                            'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-300' => $card['color'] === 'amber',
                            'bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-300' => $card['color'] === 'green',
                        ])>
                            {{ $card['value'] }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $card['label'] }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $card['hint'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div> --}}

        <div>
            {{ $this->table }}
        </div>

        @if ($isAssignmentModalOpen)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4">
                <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="flex items-start justify-between border-b border-gray-200 p-6 dark:border-gray-800">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-950 dark:text-white">Penugasan Massal</h2>
                            <p class="mt-1 text-sm text-gray-500">Pilih indikator dan unit yang akan ditugaskan.</p>
                        </div>
                        <button type="button" wire:click="closeAssignmentModal" class="text-2xl text-gray-400 hover:text-gray-600">&times;</button>
                    </div>

                    <div class="grid gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Pilih Indikator</h3>
                                <span class="text-xs text-gray-500">{{ count($selectedIndicatorIds) }} indikator dipilih</span>
                            </div>
                            <div class="max-h-72 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-800">
                                @foreach ($this->indicators as $indicator)
                                    <label class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 text-sm last:border-b-0 dark:border-gray-800">
                                        <input type="checkbox" wire:model.live="selectedIndicatorIds" value="{{ $indicator->id }}" class="rounded border-gray-300 text-primary-600">
                                        <span class="w-20 font-semibold text-primary-600">{{ $indicator->code }}</span>
                                        <span class="text-gray-700 dark:text-gray-200">{{ $indicator->statement }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <div class="mb-3 flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Pilih Unit</h3>
                                <span class="text-xs text-gray-500">{{ count($selectedUnitIds) }} unit dipilih</span>
                            </div>
                            <div class="max-h-72 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-800">
                                @foreach ($this->units as $unit)
                                    <label class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 text-sm last:border-b-0 dark:border-gray-800">
                                        <input type="checkbox" wire:model.live="selectedUnitIds" value="{{ $unit->id }}" class="rounded border-gray-300 text-primary-600">
                                        <span class="w-20 font-semibold text-gray-600 dark:text-gray-300">{{ $unit->code }}</span>
                                        <span class="text-gray-700 dark:text-gray-200">{{ $unit->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <label class="space-y-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">PIC Utama</span>
                            <select wire:model="primaryPicUnitId" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                                <option value="">Tidak ada</option>
                                @foreach ($this->units->whereIn('id', $selectedUnitIds) as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="space-y-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Batas Pengisian</span>
                            <input type="date" wire:model="dueDate" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                        </label>

                        <label class="space-y-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Prioritas</span>
                            <select wire:model="priority" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                                <option value="low">Rendah</option>
                                <option value="normal">Normal</option>
                                <option value="high">Tinggi</option>
                            </select>
                        </label>

                        <label class="space-y-1 lg:col-span-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Penugasan</span>
                            <textarea wire:model="notes" rows="3" maxlength="500" placeholder="Tulis catatan atau instruksi tambahan..." class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950"></textarea>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 p-6 dark:border-gray-800">
                        <button type="button" wire:click="closeAssignmentModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Batal</button>
                        <button type="button" wire:click="assignSelectedIndicators" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Tugaskan Sekarang</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
