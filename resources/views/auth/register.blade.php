@extends('layouts.app')
@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#071A33] p-4">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto bg-gradient-to-br from-[#155EEF] to-[#00B8A9] rounded-2xl flex items-center justify-center mb-4">
                <span class="text-white text-2xl font-bold">S1</span>
            </div>
            <h1 class="text-2xl font-bold text-white">SEMIZZY ONE</h1>
            <p class="text-gray-400 mt-1">Create your account</p>
        </div>

        {{-- Register Form --}}
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-[#071A33] mb-1.5">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155EEF] focus:border-[#155EEF] transition-colors text-sm @error('name') border-red-500 @enderror"
                        placeholder="John Doe">
                    @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-[#071A33] mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155EEF] focus:border-[#155EEF] transition-colors text-sm @error('email') border-red-500 @enderror"
                        placeholder="you@example.com">
                    @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-[#071A33] mb-1.5">Phone (optional)</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155EEF] focus:border-[#155EEF] transition-colors text-sm @error('phone') border-red-500 @enderror"
                        placeholder="08012345678">
                    @error('phone') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-[#071A33] mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155EEF] focus:border-[#155EEF] transition-colors text-sm @error('password') border-red-500 @enderror"
                        placeholder="••••••••">
                    @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-[#071A33] mb-1.5">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#155EEF] focus:border-[#155EEF] transition-colors text-sm"
                        placeholder="••••••••">
                </div>

                {{-- Submit --}}
                <button type="submit"
                    class="w-full bg-gradient-to-r from-[#155EEF] to-[#00B8A9] text-white font-semibold py-3 rounded-xl hover:opacity-90 transition-opacity focus:ring-2 focus:ring-offset-2 focus:ring-[#155EEF]">
                    Create Account
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-gray-600">
                Already have an account?
                <a href="{{ route('login') }}" class="text-[#155EEF] font-semibold hover:text-[#00B8A9]">Sign in</a>
            </p>
        </div>

        <p class="text-center text-xs text-gray-500 mt-6">
            © {{ date('Y') }} SEMIZZY WEBMASTER. All rights reserved.
        </p>
    </div>
</div>
@endsection