<x-filament-panels::page>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-medium text-gray-500 dark:text-gray-400">{{ __('Available Credits') }}</h2>
            <div class="mt-4 flex items-baseline">
                <span class="text-4xl font-extrabold text-primary-600 dark:text-primary-400">
                    {{ $user->credits }}
                </span>
                <span class="ml-2 text-gray-500">{{ __('Credits') }}</span>
            </div>
            <p class="mt-2 text-sm text-gray-400">
                {{ __('Credits will be deducted when generating AI content.') }}
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-medium text-gray-500 dark:text-gray-400">{{ __('Current Plan') }}</h2>
            <div class="mt-4">
                <span class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $currentPlan ? $currentPlan->name : __('Free Trial') }}
                </span>
            </div>
            <div class="mt-4">
                @if($currentPlan)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ __('Active') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ __('No Plan') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <x-filament::section>
        <x-slot name="heading">
            {{ __('Upgrade Plan') }}
        </x-slot>
        <x-slot name="description">
            {{ __('Choose a plan to get more credits monthly.') }}
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
            @foreach($plans as $plan)
            <div class="relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm flex flex-col p-6 hover:shadow-lg transition">
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                    <div class="mt-4 flex items-baseline text-gray-900 dark:text-white">
                        <span class="text-4xl font-extrabold tracking-tight">${{ intval($plan->price) }}</span>
                        <span class="ml-1 text-xl font-semibold text-gray-500">/mo</span>
                    </div>
                    <p class="mt-4 text-primary-600 font-medium">
                        +{{ $plan->monthly_credits }} {{ __('Credits') }} / month
                    </p>

                    <ul role="list" class="mt-6 space-y-4">
                        @if(is_array($plan->features))
                            @foreach($plan->features as $feature => $val)
                            <li class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ $feature }}</p>
                            </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
                
                <div class="mt-8">
                   <x-filament::button class="w-full" wire:click="mountAction('upgrade', { plan_id: {{ $plan->id }} })">
                        {{ __('Select {plan}', ['plan' => $plan->name]) }}
                   </x-filament::button>
                </div>
            </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-panels::page>