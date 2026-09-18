<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#f0f2f5] antialiased dark:bg-[#18191a]">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col items-center gap-4">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-3" wire:navigate>
                    <span class="flex size-16 items-center justify-center rounded-full bg-[#1877f2] text-white shadow-lg shadow-[#1877f2]/30">
                        <flux:icon.truck class="size-9" />
                    </span>

                    <span class="text-2xl font-bold tracking-tight text-[#1877f2]">
                        {{ config('app.name', 'Laravel') }}
                    </span>
                </a>

                <div
                    class="w-full rounded-xl border border-black/5 bg-white shadow-[0_2px_4px_rgba(0,0,0,.1),0_8px_16px_rgba(0,0,0,.1)] dark:border-white/10 dark:bg-[#242526]"
                    style="--color-accent:#1877f2; --color-accent-content:#1877f2; --color-accent-foreground:#ffffff;"
                >
                    <div class="px-8 py-8">{{ $slot }}</div>
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
