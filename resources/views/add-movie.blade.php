<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>RATE — Add Movie</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen p-8">
  <div class="max-w-3xl mx-auto">
    <a href="{{ url('/') }}" class="inline-block mb-6 text-sm text-white/70 hover:underline">← Back to Home</a>

    <div class="bg-gray-900 p-8 rounded-2xl shadow-lg">
      <h1 class="text-2xl font-bold mb-4">Add New Movie</h1>
      <p class="text-sm text-white/70 mb-6">Enter the movie details and click "Save". You can provide an image via URL.</p>

      <!-- Delete movie by ID (admin) -->
      <div class="mb-6 p-4 bg-gray-800 rounded-md">
        <h2 class="text-lg font-semibold mb-2">Delete a Movie</h2>
        <p class="text-sm text-white/70 mb-3">Quickly delete a movie by its ID. This action is permanent.</p>

        <div class="flex items-center gap-3">
          <input id="deleteMovieId" type="number" min="1" placeholder="Movie ID" class="w-40 bg-gray-900 border border-gray-700 rounded-md p-2 text-white" />
          <button id="deleteBtn" type="button" class="px-4 py-2 bg-red-700 hover:bg-red-600 rounded-md">Delete</button>
          <div id="deleteStatus" class="text-sm text-white/80"></div>
        </div>
      </div>

      <!-- Edit / Update movie -->
      <div class="mb-6 p-4 bg-gray-800 rounded-md">
        <h2 class="text-lg font-semibold mb-2">Edit a Movie</h2>
        <p class="text-sm text-white/70 mb-3">Load a movie by ID to edit its fields, then press <strong>Update</strong>.</p>

        <div class="flex items-center gap-3">
          <input id="loadMovieId" type="number" min="1" placeholder="Movie ID to load" class="w-40 bg-gray-900 border border-gray-700 rounded-md p-2 text-white" />
          <button id="loadBtn" type="button" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-md">Load</button>
          <button id="updateBtn" type="button" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 rounded-md" disabled>Update</button>
          <button id="clearEditBtn" type="button" class="px-3 py-2 bg-white/10 hover:bg-white/20 rounded-md">Clear</button>
          <div id="editStatus" class="text-sm text-white/80"></div>
        </div>
      </div>

      <!-- Form -->
      <form id="addMovieForm" method="POST" action="{{ route('admin.movies.store') }}" enctype="multipart/form-data" class="space-y-4">
  @csrf
  <div class="mb-4">
    <label for="visibility" class="block text-sm mb-1">Visibility</label>
    <select id="visibility" name="visibility" class="w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none">
      <option value="dashboard">Dashboard only</option>
      <option value="homepage">Homepage only</option>
      <option value="add-movie">Add Movie page</option>
      <option value="both" selected>Both (Dashboard + Homepage)</option>
    </select>
    <p class="text-xs text-white/60 mt-1">Choose where this movie's card should appear.</p>
  </div>

  @php
    // Expecting the controller to pass $categories (collection of Category models).
  // Make categories unique by name to avoid duplicate visible names in the dropdown
  // (some scripts or past imports created multiple rows that show the same name).
    // The view intentionally does not write to the database.
    $categories = $categories ?? collect();
  // keep the first occurrence per name and reindex the collection
  $categories = $categories->unique('name')->values();
  // Remove the legacy 'disney-plus' helper category from the selector so
  // admins no longer see the "Disney Plus" option in the Add Movie form.
  $categories = $categories->reject(function($c){ return isset($c->slug) && $c->slug === 'disney-plus'; })->values();
  @endphp

  <div class="mb-4">
    <label for="category_id" class="block text-sm mb-1">Category</label>
    <select id="category_id" name="category_id" class="w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none">
      <option value="" {{ old('category_id') ? '' : 'selected' }}>None</option>
      @foreach($categories as $cat)
        <option value="{{ $cat->id }}" {{ (string)old('category_id') === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
      @endforeach
      @php
        // If the DB doesn't yet contain a Horror category, show an inline hint so admin
        // knows how to add it. The seeder below creates a proper 'horror' category.
        $hasHorror = $categories->contains(function($c){ return isset($c->slug) && $c->slug === 'horror'; });
      @endphp
      @if(!$hasHorror)
        <option disabled>Horror (not found in DB — run CategorySeeder)</option>
      @endif
    </select>
    <p class="text-xs text-white/60 mt-1">Assign the movie to a category (e.g. Marvel Cinematic Universe or Disney+ Originals).</p>
  </div>

  

  <div>
    <label class="block text-sm mb-1">Title</label>
    <input id="title" name="title" type="text" required
           class="w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none" />
  </div>

  <div>
    <label class="block text-sm mb-1">Description</label>
    <textarea id="description" name="description" rows="4" required
              class="w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none"></textarea>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm mb-1">User Score (0 - 10)</label>
      <input id="user_score" name="user_score" type="number" step="0.1" min="0" max="10" required
             class="w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none" />
    </div>

    <div>
      <label class="block text-sm mb-1">Poster Image (optional)</label>

      <!-- الحاوية -->
      <div class="flex items-center gap-3 min-w-0">
        <!-- زر مخصص -->
        <label for="image" class="cursor-pointer bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition">
          🗂
        </label>

        <!-- النص اللي هيظهر -->
        <span id="fileName" class="text-sm text-white/70 truncate" style="max-width: 100%;">لم يتم اختيار أي ملف</span>
      </div>

      <!-- مدخل الملف الحقيقي (مخفي) -->
      <input id="image" name="image" type="file" accept="image/*" class="hidden" />
    </div>
  </div>

  <div class="mb-4 flex items-center gap-3">
    <input type="checkbox" id="is_featured" name="is_featured" value="1" class="w-4 h-4">
    <label for="is_featured" class="text-sm text-white/80">Show on homepage (Featured)</label>
  </div>

  <div class="mb-4">
    <label for="dashboard_id" class="block text-sm mb-1">Dashboard (optional)</label>
    <select id="dashboard_id" name="dashboard_id" class="w-full bg-gray-800 border border-gray-700 rounded-md p-3 text-white focus:outline-none">
      <option value="">None</option>
      <option value="1">Dashboard 1</option>
      <option value="2">Dashboard 2 — Marvel Cinematic Universe</option>
    </select>
    <p class="text-xs text-white/60 mt-1">Assign this movie to a specific dashboard. Use Dashboard 2 for MCU grouping.</p>
  </div>

  <div class="flex items-center gap-3">
    <button id="submitBtn" type="submit"
      class="px-5 py-3 bg-red-600 text-white rounded-md hover:bg-red-700 transition">Save Movie</button>
    <button id="resetBtn" type="button"
      class="px-4 py-3 bg-white/10 text-white rounded-md hover:bg-white/20 transition">Reset</button>
    <div id="status" class="text-sm text-white/80"></div>
  </div>
</form>

    </div>
  </div>

  @vite(['resources/js/add-movie.js'])
</body>
</html>
