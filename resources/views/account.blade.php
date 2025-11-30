@extends('layouts.app')

@section('title','Account - RATE')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-4">Account</h1>
    <div class="bg-white/5 p-6 rounded">
        <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
    </div>
</div>
@endsection
