{{--
  Spec: صفحة تفاصيل الفيلم — مخطط عرض `show.blade.php`
  هذا الملف مخصّص كمخطط (spec) لتطبيق التصميم في Blade.
  يحتوي الأماكن المتوقعة للبيانات، التعليقات، واقتراحات التحميل المتأخر.
--}}

@extends('layouts.app')

@section('title', $movie->title ?? 'تفاصيل الفيلم')

@section('content')
<main class="movie-page container">
  {{-- Hero: Poster + Title + Primary Actions --}}
  <section class="hero">
    <div class="poster">
      <img src="{{ $movie->poster_path ?? '/storage/default-poster.jpg' }}" alt="{{ $movie->title }} poster">
    </div>
    <div class="meta">
      <h1>{{ $movie->title }} <small>({{ optional($movie)->release_date ? 
        ":" : '' }})</small></h1>
      <p class="tags">@foreach($movie->genres ?? [] as $g)<span>{{ $g->name }}</span>@endforeach</p>
      <div class="actions">
        <button class="btn">مشاهدة المقطورة</button>
        <button class="btn">أضف للمفضلة</button>
        <button class="btn">قييم</button>
      </div>
    </div>
  </section>

  {{-- Quick Facts + Rating Summary (تحميل أساسي) --}}
  <section class="quick-facts">
    <ul>
      <li>المدة: {{ $movie->runtime ?? '-' }} دقيقة</li>
      <li>التصنيفات: {{ implode(', ', $movie->genres->pluck('name')->toArray() ?? []) }}</li>
      <li>المتوسط: {{ $movie->avg_rating ?? '—' }} ({{ $movie->rating_count ?? 0 }})</li>
    </ul>
  </section>

  {{-- Synopsis (اقرأ المزيد) --}}
  <section class="synopsis">
    <h2>نبذة</h2>
    <p>{{ Str::limit($movie->overview ?? 'لا يوجد وصف متاح', 300) }}</p>
    <button class="btn-small" id="read-more">اقرأ المزيد</button>
  </section>

  {{-- Lazy-load sections: Trailer, Gallery, Reviews, Cast --}}
  <div id="lazy-sections">
    {{-- Trailer & Gallery (يجلب عبر AJAX عند الطلب) --}}
    <section id="media" class="lazy"></section>

    {{-- Cast --}}
    <section id="cast" class="lazy"></section>

    {{-- User Reviews --}}
    <section id="reviews" class="lazy"></section>
  </div>

</main>

@push('scripts')
<script>
  // مثال: تحميل lazy sections عند تمرير الصفحة أو الضغط
  document.getElementById('read-more').addEventListener('click', function(){
    // استدعاء API لعرض الوصف الكامل أو فتح modal
  });
</script>
@endpush

@endsection
