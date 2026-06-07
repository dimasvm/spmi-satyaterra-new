<x-filament-panels::page>
    <div class="space-y-6">
        <div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Informasi Indikator</h2>
                    <div class="mt-5 flex gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-blue-50 text-xl font-semibold text-blue-600 dark:bg-blue-950">ID</div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-md bg-blue-50 px-2 py-1 font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300">{{ $indicator->code }}</span>
                                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $indicator->statement }}</h3>
                            </div>
                            <dl class="mt-5 grid gap-4 text-sm md:grid-cols-2">
                                <div>
                                    <dt class="text-gray-500">Standar</dt>
                                    <dd class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $indicator->qualityStandard?->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Target</dt>
                                    <dd class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $indicator->target_operator?->value }} {{ $indicator->target_value }} {{ $indicator->target_unit }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Periode</dt>
                                    <dd class="mt-1 font-medium text-gray-900 dark:text-gray-100">{{ $this->assignments->first()?->spmiPeriod?->name ?? '-' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-200 dark:bg-green-950 dark:text-green-300">
                                            {{ $this->responseSummary['completed'] }} dari {{ $this->responseSummary['total'] }} selesai
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Unit yang Ditugaskan</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-950">
                                <tr>
                                    <th class="px-4 py-3">Unit</th>
                                    <th class="px-4 py-3">Peran</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Progress</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($this->assignments as $assignment)
                                    @php($progress = $this->progressFor($assignment))
                                    <tr>
                                        <td class="px-4 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $assignment->unit?->name }}</td>
                                        <td class="px-4 py-4">
                                            <span @class([
                                                'rounded-md px-2 py-1 text-xs font-medium',
                                                'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300' => $assignment->is_primary_pic,
                                                'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => ! $assignment->is_primary_pic,
                                            ])>{{ $assignment->is_primary_pic ? 'PIC Utama' : 'Unit Terlibat' }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950 dark:text-blue-300">{{ $assignment->status->getLabel() }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center gap-3">
                                                <span class="w-10 text-xs font-medium">{{ $progress }}%</span>
                                                <div class="h-2 w-32 rounded-full bg-gray-100 dark:bg-gray-800">
                                                    <div class="h-2 rounded-full bg-primary-600" style="width: {{ $progress }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex justify-end gap-2">
                                            <button
                                                type="button"
                                                wire:click="openEditAssignmentModal({{ $assignment->id }})"
                                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                                            >
                                                Edit
                                            </button>
                                                <button
                                                    type="button"
                                                    wire:click="deleteAssignment({{ $assignment->id }})"
                                                    wire:confirm="Hapus penugasan unit ini? Data capaian, bukti, dan timeline terkait penugasan ini akan ikut terhapus."
                                                    class="inline-flex items-center justify-center rounded-lg border border-danger-300 px-3 py-2 text-xs font-semibold text-danger-700 hover:bg-danger-50 dark:border-danger-700 dark:text-danger-300 dark:hover:bg-danger-950"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada unit ditugaskan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Ringkasan Respon Unit</h2>
                    <div class="mt-5 h-3 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div class="h-3 bg-green-500" style="width: {{ $this->responseSummary['completion_percent'] }}%"></div>
                    </div>
                    <p class="mt-4 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $this->responseSummary['completed'] }} dari {{ $this->responseSummary['total'] }} unit sudah selesai.</p>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Ringkasan Penugasan</h2>
                    @php($firstAssignment = $this->assignments->first())
                    <dl class="mt-4 divide-y divide-gray-100 text-sm dark:divide-gray-800">
                        <div class="flex justify-between gap-4 py-3"><dt class="text-gray-500">Tanggal Penugasan</dt><dd class="font-medium">{{ $firstAssignment?->assigned_at?->translatedFormat('d M Y, H:i') ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="text-gray-500">Deadline</dt><dd class="font-medium">{{ $firstAssignment?->due_date?->translatedFormat('d M Y') ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="text-gray-500">Prioritas</dt><dd class="font-medium">{{ $firstAssignment?->priority?->getLabel() ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-4 py-3"><dt class="text-gray-500">Dibuat Oleh</dt><dd class="font-medium">{{ $firstAssignment?->assignedBy?->name ?? '-' }}</dd></div>
                    </dl>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Timeline Aktivitas</h2>
                    <div class="mt-5 space-y-4">
                        @forelse ($this->timeline as $event)
                            <div class="flex gap-3">
                                <span class="mt-1 h-3 w-3 rounded-full bg-primary-600"></span>
                                <div class="min-w-0 text-sm">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $event['description'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $event['occurred_at']?->translatedFormat('d M Y, H:i') ?? '-' }} - {{ $event['unit'] }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada aktivitas.</p>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Aksi Cepat</h2>
                    <div class="mt-4 grid gap-3">
                        <button wire:click="sendReminder" type="button" class="rounded-lg border border-primary-600 px-4 py-2 text-sm font-semibold text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-950">Kirim Reminder</button>
                        <button wire:click="openFirstAssignmentEditModal" type="button" class="rounded-lg bg-primary-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-primary-500">Ubah Penugasan</button>
                    </div>
                </section>
            </aside>
        </div>

        @if ($isEditAssignmentModalOpen)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50 p-4">
                <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-xl bg-white shadow-xl dark:bg-gray-900">
                    <div class="flex items-start justify-between border-b border-gray-200 p-6 dark:border-gray-800">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-950 dark:text-white">Edit Penugasan</h2>
                            <p class="mt-1 text-sm text-gray-500">Perbarui unit, periode, peran, status, dan batas pengisian.</p>
                        </div>
                        <button type="button" wire:click="closeEditAssignmentModal" class="text-2xl text-gray-400 hover:text-gray-600">&times;</button>
                    </div>

                    <div class="grid gap-5 p-6 md:grid-cols-2">
                        <label class="space-y-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Unit</span>
                            <select wire:model="editUnitId" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                                @foreach ($this->units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('editUnitId')
                                <span class="text-xs text-danger-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Periode SPMI</span>
                            <select wire:model="editPeriodId" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                                @foreach ($this->periods as $periodOption)
                                    <option value="{{ $periodOption->id }}">{{ $periodOption->name }}</option>
                                @endforeach
                            </select>
                            @error('editPeriodId')
                                <span class="text-xs text-danger-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Batas Pengisian</span>
                            <input type="date" wire:model="editDueDate" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                            @error('editDueDate')
                                <span class="text-xs text-danger-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</span>
                            <select wire:model="editStatus" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                                @foreach (\App\Enums\IndicatorAssignmentStatus::cases() as $statusOption)
                                    <option value="{{ $statusOption->value }}">{{ $statusOption->getLabel() }}</option>
                                @endforeach
                            </select>
                            @error('editStatus')
                                <span class="text-xs text-danger-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="space-y-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Prioritas</span>
                            <select wire:model="editPriority" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                                @foreach (\App\Enums\IndicatorAssignmentPriority::cases() as $priorityOption)
                                    <option value="{{ $priorityOption->value }}">{{ $priorityOption->getLabel() }}</option>
                                @endforeach
                            </select>
                            @error('editPriority')
                                <span class="text-xs text-danger-600">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 text-sm dark:border-gray-800">
                            <input type="checkbox" wire:model="editIsPrimaryPic" class="rounded border-gray-300 text-primary-600">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Jadikan PIC Utama</span>
                        </label>

                        <label class="space-y-1 md:col-span-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan Penugasan</span>
                            <textarea wire:model="editNotes" rows="4" maxlength="500" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950"></textarea>
                            @error('editNotes')
                                <span class="text-xs text-danger-600">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 p-6 dark:border-gray-800">
                        <button type="button" wire:click="closeEditAssignmentModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-200">Batal</button>
                        <button type="button" wire:click="updateAssignment" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Simpan Perubahan</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
