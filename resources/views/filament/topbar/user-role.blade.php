@php
    use Illuminate\Support\Str;

    $roleText = auth()->user()
        ?->getRoleNames()
        ->map(fn (string $role): string => Str::of($role)->replace(['_', '-'], ' ')->headline()->toString())
        ->join(', ');
@endphp

<x-filament::badge>
    {{ filled($roleText) ? $roleText : 'Tanpa Role' }}
</x-filament::badge>
