<div wire:poll.15s class="flex items-center gap-x-3 px-3 py-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full shadow-sm">
    <div class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/30">
        <x-heroicon-o-banknotes class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
    </div>
    <div class="flex flex-col">
        <span class="text-[10px] font-medium text-gray-500 uppercase leading-none">Credits</span>
        <span class="text-sm font-bold text-gray-950 dark:text-white leading-tight">
            {{ number_format(auth()->user()->credits ?? 0) }}
        </span>
    </div>
</div>