@extends('layouts.app')

@section('title', 'Create account — RATE')

@section('content')
<div class="min-h-[70vh] flex items-center">
        <div class="max-w-lg mx-auto w-full px-6">
            <div class="bg-black/80 border border-white/8 rounded-xl p-8 shadow-lg">
                <h3 class="text-2xl font-bold mb-2">Create account</h3>
                <p class="text-sm text-gray-400 mb-4">Sign up to start saving movies and building playlists.</p>

                @if($errors->any())
                    <div class="mb-4 p-3 bg-yellow-900/40 border border-yellow-700 rounded text-yellow-200">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('signup.post') }}" novalidate>
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm text-gray-300 mb-1">Full Name</label>
                        <input name="name" value="{{ old('name') }}" required class="w-full rounded-md bg-gray-800 border border-white/10 p-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rate-red">
                        @error('name') <p class="text-yellow-300 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-300 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-md bg-gray-800 border border-white/10 p-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rate-red">
                        @error('email') <p class="text-yellow-300 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-gray-300 mb-1">Password</label>
                        <input type="password" name="password" required class="w-full rounded-md bg-gray-800 border border-white/10 p-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rate-red">
                        <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long.</p>
                        @error('password') <p class="text-yellow-300 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm text-gray-300 mb-1">Confirm password</label>
                        <input type="password" name="password_confirmation" required class="w-full rounded-md bg-gray-800 border border-white/10 p-3 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-rate-red">
                    </div>

                    <button class="w-full py-3 bg-rate-red rounded-md font-semibold hover:bg-rate-red-hover transition">Create account</button>
                </form>

                <div class="mt-4 text-center text-sm text-gray-400">
                    Already have an account? <a href="{{ route('login') }}" class="text-white underline">Sign in</a>
                </div>
            </div>
        </div>
</div>
@endsection
