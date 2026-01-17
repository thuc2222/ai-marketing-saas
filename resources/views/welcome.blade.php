<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app(App\Settings\GeneralSettings::class)->site_name ?? config('app.name') }} - {{ __('Ultimate Content Automation') }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-display { font-family: 'Outfit', sans-serif; }
        .bg-tech-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 24px 24px;
        }
        .text-gradient-ai {
            background: linear-gradient(to right, #4f46e5, #9333ea, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 overflow-x-hidden">

    @php
        $settings = app(App\Settings\GeneralSettings::class);
    @endphp

    <nav class="bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600 shadow-lg fixed w-full z-50 transition-all duration-300 ease-in-out">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 md:h-20 items-center">
                
                <div class="flex items-center flex-shrink-0">
                    <a href="/" class="flex items-center space-x-2 group">
                        @if($settings->site_logo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->site_logo) }}" 
                                 alt="{{ $settings->site_name }}" 
                                 class="h-10 w-auto rounded-lg bg-white/20 p-1 backdrop-blur-sm group-hover:bg-white/30 transition shadow-sm">
                        @else
                            <div class="bg-white/20 p-2 rounded-xl backdrop-blur-sm group-hover:bg-white/30 transition">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10 4 10 0 0 1-5-7.64L12 2v20"></path></svg>
                            </div>
                        @endif

                        <span class="font-display font-bold text-xl md:text-2xl text-white tracking-tight">
                            {{ $settings->site_name ?? config('app.name', 'AI Marketing') }}
                        </span>
                    </a>
                </div>

                <div class="flex items-center space-x-3 md:space-x-6">
                    
                    <div class="hidden md:flex items-center space-x-2 mr-2 bg-black/10 px-3 py-1 rounded-full">
                        <a href="{{ route('switch-language', 'en') }}" class="text-sm {{ app()->getLocale() == 'en' ? 'font-bold text-white' : 'text-white/60 hover:text-white' }}">EN</a>
                        <span class="text-white/40 text-xs">|</span>
                        <a href="{{ route('switch-language', 'vi') }}" class="text-sm {{ app()->getLocale() == 'vi' ? 'font-bold text-white' : 'text-white/60 hover:text-white' }}">VI</a>
                    </div>

                    @auth
                        <a href="/admin" class="font-medium text-white hover:text-white/80 transition px-3 py-2 rounded-lg hover:bg-white/10">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="/admin/login" class="hidden md:inline-block font-medium text-white hover:text-white/80 transition px-4 py-2 rounded-full hover:bg-white/10">
                            {{ __('Log In') }}
                        </a>
                        <a href="/admin/register" class="bg-white text-orange-600 px-5 py-2.5 rounded-full font-bold hover:bg-gray-100 hover:scale-105 hover:shadow-md transition duration-300 ease-in-out">
                            {{ __('Start Free Trial') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <div class="h-16 md:h-20"></div>

    <div class="relative overflow-hidden bg-tech-pattern pt-16 pb-24 lg:pt-32 lg:pb-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-center">
                <div class="sm:text-center md:max-w-2xl md:mx-auto lg:col-span-6 lg:text-left">
                    <div class="inline-flex items-center text-sm font-semibold py-1 px-3 mb-4 rounded-full bg-indigo-100 text-indigo-700">
                        <span class="bg-indigo-600 text-white text-xs px-2 py-0.5 rounded-full mr-2">New</span>
                        {{ __('Latest GPT-4 Technology') }}
                    </div>
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl lg:text-5xl xl:text-6xl">
                        <span class="block">{{ __('Automate Marketing') }}</span>
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 font-display">
                            {{ __('with AI Power') }}
                        </span>
                    </h1>
                    <p class="mt-6 text-lg text-gray-600 sm:max-w-xl sm:mx-auto lg:mx-0">
                        {{ __('Plan 30 days of content in 5 minutes. Generate viral TikTok scripts, Facebook captions, and auto-publish. Free up your time today.') }}
                    </p>
                    <div class="mt-10 sm:flex sm:justify-center lg:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                        <div class="rounded-full shadow-xl">
                            <a href="/admin/register" class="w-full flex items-center justify-center px-8 py-4 border border-transparent text-lg font-bold rounded-full text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition duration-300 ease-in-out hover:scale-105 hover:shadow-2xl md:py-4 md:text-xl md:px-10">
                                {{ __('Get Started Today') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 -mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="mt-16 sm:mt-24 lg:mt-0 lg:col-span-6 relative">
                    <div class="absolute top-0 left-0 w-72 h-72 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
                    <div class="absolute top-0 right-0 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
                    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
                    
                    <div class="relative mx-auto w-full rounded-3xl shadow-2xl lg:max-w-md bg-gradient-to-tr from-indigo-500 via-purple-500 to-pink-500 p-3 sm:p-4 animate-tilt transform hover:scale-[1.02] transition duration-500">
                        <div class="rounded-2xl bg-white/90 backdrop-blur-xl overflow-hidden shadow-inner h-full p-6 relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <div class="h-8 w-24 bg-gray-200 rounded-lg animate-pulse"></div>
                                <div class="flex space-x-2">
                                    <div class="h-8 w-8 bg-blue-100 rounded-full animate-pulse"></div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="h-32 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-2xl animate-pulse flex items-center justify-center">
                                    <span class="text-indigo-300 font-semibold">{{ __('AI Generating Video...') }}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="h-20 bg-gray-100 rounded-xl animate-pulse"></div>
                                    <div class="h-20 bg-gray-100 rounded-xl animate-pulse"></div>
                                    <div class="h-20 bg-indigo-50 rounded-xl border-2 border-indigo-200 animate-pulse relative">
                                        <div class="absolute -top-2 -right-2 bg-green-500 w-6 h-6 rounded-full border-2 border-white"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute bottom-10 right-10 bg-white rounded-2xl shadow-lg p-4 flex items-center space-x-3 animate-bounce hover:pause">
                                <div class="bg-green-100 p-2 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">{{ __('Success!') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('Published to TikTok') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-24 relative z-10 bg-gray-50 overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-gradient-to-tr from-indigo-100/40 to-pink-100/40 rounded-full filter blur-3xl -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <h2 class="text-indigo-600 font-semibold tracking-wide uppercase mb-3">{{ __('Flexible Pricing') }}</h2>
                <p class="font-display text-4xl font-extrabold text-gray-900 sm:text-5xl mb-6">
                    {{ __('Choose the plan that fits your') }} <span class="text-gradient-ai">{{ __('growth') }}</span>
                </p>
                <p class="text-xl text-gray-500">{{ __('No hidden fees. Cancel anytime.') }}</p>
            </div>

            <div class="mt-16 space-y-8 md:space-y-0 md:grid md:grid-cols-2 md:gap-8 lg:max-w-4xl lg:mx-auto xl:max-w-none xl:grid-cols-3">
                @foreach($plans as $index => $plan)
                <div class="relative bg-white rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 ease-in-out transform hover:-translate-y-2 border {{ $index === 1 ? 'border-indigo-200 ring-4 ring-indigo-500/20 z-10 md:scale-105' : 'border-gray-100' }} flex flex-col">
                    
                    @if($index === 1)
                    <div class="absolute top-0 right-10 -translate-y-1/2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-bold px-4 py-1 rounded-full shadow-lg">
                        {{ __('Most Popular') }}
                    </div>
                    @endif

                    <div class="p-8 flex-1">
                        <h3 class="text-2xl leading-6 font-display font-bold text-gray-900">{{ $plan->name }}</h3>
                        <p class="mt-6 flex items-baseline">
                            <span class="text-5xl font-extrabold tracking-tight text-gray-900">
                                {{ $settings->currency_code == 'VND' ? number_format($plan->price) : intval($plan->price) }}
                            </span>
                            <span class="ml-1 text-2xl font-bold text-gray-800">
                                {{ $settings->currency_code == 'VND' ? 'đ' : '$' }}
                            </span>
                            <span class="ml-1 text-xl font-medium text-gray-500">/{{ __('mo') }}</span>
                        </p>
                        <div class="mt-6 inline-flex items-center bg-indigo-50 rounded-xl p-3 w-full">
                            <div class="bg-indigo-100 p-2 rounded-lg mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div>
                                <p class="text-indigo-900 font-bold text-lg">{{ number_format($plan->monthly_credits) }} {{ __('AI Credits') }}</p>
                                <p class="text-indigo-600 text-sm">~ {{ $plan->monthly_credits }} {{ __('AI Posts') }}</p>
                            </div>
                        </div>

                        <ul class="mt-8 space-y-4">
                            @if($plan->features)
                                @foreach($plan->features as $feature => $desc)
                                <li class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="bg-gradient-to-br from-green-400 to-cyan-500 rounded-full p-1">
                                            <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="ml-3 text-base text-gray-700">
                                        <span class="font-medium">{{ $feature }}</span>
                                        @if($desc && $desc !== 'True' && $desc !== 'Có')
                                            <span class="text-gray-500 text-sm"> - {{ $desc }}</span>
                                        @endif
                                    </p>
                                </li>
                                @endforeach
                            @else
                                <li class="text-gray-400 italic pl-1">{{ __('Feature list updating...') }}</li>
                            @endif
                        </ul>
                    </div>
                    
                    <div class="p-8 pt-0">
                        <a href="/admin/register" class="block w-full py-4 px-6 text-center rounded-2xl font-bold text-lg text-white transition duration-300 ease-in-out hover:shadow-lg hover:scale-[1.02] {{ $index === 1 ? 'bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700' : 'bg-gray-900 hover:bg-gray-800' }}">
                            @if(intval($plan->price) == 0)
                                {{ __('Start Free Trial') }}
                            @else
                                {{ __('Select') }} {{ $plan->name }}
                            @endif
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <footer class="bg-gray-900 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-400">
            <p class="font-display text-2xl text-white font-bold mb-4">
                {{ $settings->site_name ?? config('app.name') }}
            </p>
            <p>&copy; {{ date('Y') }} {{ $settings->site_name ?? config('app.name') }}. All rights reserved.</p>
            <div class="mt-4 flex justify-center space-x-6">
                <a href="#" class="hover:text-white transition">{{ __('Terms') }}</a>
                <a href="#" class="hover:text-white transition">{{ __('Privacy') }}</a>
                <a href="#" class="hover:text-white transition">{{ __('Contact') }}</a>
            </div>
        </div>
    </footer>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: { blob: "blob 7s infinite", tilt: 'tilt 10s infinite linear' },
                    keyframes: {
                        blob: {
                            "0%": { transform: "translate(0px, 0px) scale(1)" },
                            "33%": { transform: "translate(30px, -50px) scale(1.1)" },
                            "66%": { transform: "translate(-20px, 20px) scale(0.9)" },
                            "100%": { transform: "translate(0px, 0px) scale(1)" },
                        },
                        tilt: {
                            '0%, 50%, 100%': { transform: 'rotate(0deg)' },
                            '25%': { transform: 'rotate(1deg)' },
                            '75%': { transform: 'rotate(-1deg)' },
                        }
                    },
                },
            },
        }
    </script>
</body>
</html>