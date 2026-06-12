<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-m-check">
                Simpan Role
            </x-filament::button>

            <x-filament::button
                tag="a"
                :href="\App\Filament\Pages\RolePermissionManager::getUrl()"
                color="gray"
                outlined
                icon="heroicon-m-arrow-left"
            >
                Batal
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
