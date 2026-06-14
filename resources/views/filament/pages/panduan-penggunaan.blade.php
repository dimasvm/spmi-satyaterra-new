<x-filament-panels::page>
    <x-filament::section>
        <div
            class="max-w-5xl space-y-5 text-sm leading-7 text-gray-700 dark:text-gray-300
                [&_a]:font-semibold [&_a]:text-primary-600 [&_a]:underline [&_a]:underline-offset-4 dark:[&_a]:text-primary-400
                [&_code]:rounded [&_code]:bg-gray-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:text-xs [&_code]:font-semibold [&_code]:text-gray-950 dark:[&_code]:bg-gray-800 dark:[&_code]:text-white
                [&_h1]:text-3xl [&_h1]:font-bold [&_h1]:tracking-tight [&_h1]:text-gray-950 dark:[&_h1]:text-white
                [&_h2]:mt-10 [&_h2]:border-t [&_h2]:border-gray-200 [&_h2]:pt-8 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:tracking-tight [&_h2]:text-gray-950 dark:[&_h2]:border-gray-800 dark:[&_h2]:text-white
                [&_h3]:mt-8 [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-gray-950 dark:[&_h3]:text-white
                [&_li]:my-1 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-6 [&_p]:my-4
                [&_strong]:font-semibold [&_strong]:text-gray-950 dark:[&_strong]:text-white
                [&_table]:my-5 [&_table]:w-full [&_table]:overflow-hidden [&_table]:rounded-lg [&_table]:text-left [&_table]:text-sm
                [&_td]:border-t [&_td]:border-gray-200 [&_td]:px-4 [&_td]:py-3 dark:[&_td]:border-gray-800
                [&_th]:bg-gray-50 [&_th]:px-4 [&_th]:py-3 [&_th]:font-semibold [&_th]:text-gray-950 dark:[&_th]:bg-gray-800 dark:[&_th]:text-white
                [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-6"
        >
            {!! $this->guideHtml() !!}
        </div>
    </x-filament::section>
</x-filament-panels::page>
