@php
    $logoUrl = asset('images/logo-kampus.png');
@endphp

<x-filament-panels::page.simple>
    <div class="min-h-screen bg-gray-50 text-gray-950 dark:bg-gray-950 dark:text-white">
        <div class="mx-auto grid min-h-screen w-full max-w-6xl grid-cols-1 lg:grid-cols-[1.05fr_0.95fr]">
            <section class="hidden flex-col justify-between border-r border-gray-200 bg-white px-10 py-12 dark:border-gray-800 dark:bg-gray-900 lg:flex">
                <div class="space-y-10">
                    <div class="flex items-center gap-4">
                        <img src="{{ $logoUrl }}" alt="Logo Kampus" class="h-16 w-auto">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-wide text-primary-700 dark:text-primary-300">
                                SPMI Kampus
                            </p>
                            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                Sistem Penjaminan Mutu Internal
                            </h1>
                        </div>
                    </div>

                    <div class="max-w-xl space-y-5">
                        <p class="text-base leading-7 text-gray-600 dark:text-gray-300">
                            Portal akademik untuk memantau standar mutu, capaian indikator, audit internal, tindak lanjut, dan peningkatan berkelanjutan.
                        </p>

                        <div class="grid gap-3">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
                                <div class="text-sm font-medium text-gray-950 dark:text-white">PPEPP Terintegrasi</div>
                                <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                    Penetapan, pelaksanaan, evaluasi, pengendalian, dan peningkatan mutu dalam satu ruang kerja.
                                </p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950">
                                <div class="text-sm font-medium text-gray-950 dark:text-white">Data Mutu Tertelusur</div>
                                <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                    Setiap capaian, temuan, dan revisi standar tersimpan sebagai riwayat kerja mutu kampus.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Pusat data mutu akademik untuk pengambilan keputusan yang tertib dan terukur.
                </p>
            </section>

            <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex justify-center lg:hidden">
                        <img src="{{ $logoUrl }}" alt="Logo Kampus" class="h-16 w-auto">
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-8">
                        <div class="mb-7 text-center">
                            <img src="{{ $logoUrl }}" alt="Logo Kampus" class="mx-auto hidden h-14 w-auto lg:block">
                            <h2 class="mt-5 text-xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                Masuk ke SPMI
                            </h2>
                            <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                Gunakan akun kampus untuk mengakses dashboard mutu.
                            </p>
                        </div>

                        {{ $this->content }}
                    </div>

                    <p class="mt-6 text-center text-xs leading-5 text-gray-500 dark:text-gray-400">
                        Akses terbatas untuk pengelola mutu, auditor, pimpinan, dan unit terkait.
                    </p>
                </div>
            </section>
        </div>
    </div>

</x-filament-panels::page.simple>
