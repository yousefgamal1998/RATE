
@extends('layouts.app')

@section('title', 'About Us - RATE')

@section('content')
    <section class="pt-8 pb-12">
        <div class="max-w-6xl mx-auto px-6 text-left" dir="ltr">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4">About Us</h1>
                <p class="text-gray-300 text-lg md:text-xl leading-relaxed">RATE is your destination for discovering, rating and sharing a passion for movies and TV shows. We aim to deliver a smart, organized and enjoyable viewing experience for cinephiles everywhere.</p>
                <div class="mt-6 flex gap-4">
                    <a href="mailto:hello@rate.example" class="inline-flex items-center gap-3 px-5 py-3 rounded-md text-white font-medium cta shadow-lg">Contact Us</a>
                    <a href="/" class="inline-flex items-center gap-2 px-4 py-3 rounded-md border border-white/10 text-white/90">Back to Home</a>
                </div>
            </div>

            <!-- Mission / Vision / Values -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-2">Our Mission</h3>
                    <p class="text-gray-300 text-sm">Empower viewers to discover new works quickly, share honest ratings, and build a community that values quality content and meaningful discussion.</p>
                </div>

                <div class="card p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-2">Our Vision</h3>
                    <p class="text-gray-300 text-sm">To become the trusted reference in the region for discovering films and shows — where great taste, accurate data and delightful UX come together.</p>
                </div>

                <div class="card p-6 rounded-lg">
                    <h3 class="text-xl font-semibold mb-2">Our Values</h3>
                    <ul class="text-gray-300 text-sm space-y-2">
                        <li>• Transparency & Reliability</li>
                        <li>• Respect for Diversity</li>
                        <li>• User-Centered Design</li>
                        <li>• Passion for Cinema</li>
                    </ul>
                </div>
            </div>

            <!-- Team -->
            <div class="mt-12 border-t border-white/6 pt-8">
                <h2 class="text-2xl font-bold mb-6">Our Team</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <figure class="card p-4 rounded-lg flex items-center gap-4">
                        <img src="{{ asset('image/placeholder.png') }}" alt="Team" class="w-16 h-16 rounded-full object-cover">
                        <figcaption>
                            <div class="font-semibold">Ahmed Ali</div>
                            <div class="text-xs text-gray-300">Founder & Product Lead</div>
                        </figcaption>
                    </figure>

                    <figure class="card p-4 rounded-lg flex items-center gap-4">
                        <img src="{{ asset('image/placeholder.png') }}" alt="Team" class="w-16 h-16 rounded-full object-cover">
                        <figcaption>
                            <div class="font-semibold">Sarah Mohamed</div>
                            <div class="text-xs text-gray-300">Design & UX</div>
                        </figcaption>
                    </figure>

                    <figure class="card p-4 rounded-lg flex items-center gap-4">
                        <img src="{{ asset('image/placeholder.png') }}" alt="Team" class="w-16 h-16 rounded-full object-cover">
                        <figcaption>
                            <div class="font-semibold">Khaled Samir</div>
                            <div class="text-xs text-gray-300">Engineering</div>
                        </figcaption>
                    </figure>
                </div>
            </div>
        </div>
    </section>
@endsection

