<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $category->name ?? 'Category' }} - RATE</title>
  <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-black text-white min-h-screen">
  <header class="py-6 px-6 border-b border-white/10">
    <div class="max-w-6xl mx-auto">
      <a href="{{ route('dashboard') }}" class="text-rate-red font-bold text-2xl">RATE</a>
    </div>
  </header>

  <main class="max-w-7xl mx-auto p-6">
    <h1 class="text-4xl font-bold mb-4">{{ $category->name }}</h1>
    @if(!empty($category->description))
      <p class="text-gray-300 mb-6">{{ $category->description }}</p>
    @endif

    @if(isset($movies) && $movies->count())
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($movies as $movie)
          <a href="{{ route('movies.show', $movie->id) }}" class="block bg-white/5 rounded overflow-hidden hover:shadow-lg transition">
            <img src="{{ $movie->image_url ?? asset('image/placeholder.png') }}" alt="{{ $movie->title }}" class="w-full h-48 object-cover">
            <div class="p-3">
              <h2 class="font-semibold">{{ $movie->title }}</h2>
              <p class="text-xs text-gray-400 mt-1">{{ Str::limit($movie->description, 80) }}</p>
            </div>
          </a>
        @endforeach
      </div>
    @else
      <div class="text-gray-400">No movies found in this category yet.</div>
    @endif
  </main>

  <footer class="text-center text-sm text-gray-500 p-6">© {{ date('Y') }} RATE</footer>
</body>
</html>
