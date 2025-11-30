<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovieController;

// ✅ نقطة ترحيبية لتجربة الـ API
Route::get('/', function () {
    return response()->json(['message' => 'Welcome to Movie API 🎬']);
});

// ✅ كل المسارات داخل middleware api
Route::middleware('api')->group(function () {
    // عرض كل الأفلام
    Route::get('/movies', [MovieController::class, 'index']);
    
    // عرض فيلم واحد
    Route::get('/movies/{id}', [MovieController::class, 'show']);
    
    // إضافة فيلم جديد
    Route::post('/movies', [MovieController::class, 'store']);
    
    // تعديل فيلم
    Route::put('/movies/{id}', [MovieController::class, 'update']);
    
    // حذف فيلم
    Route::delete('/movies/{id}', [MovieController::class, 'destroy']);

    // ✅ حذف كل الأفلام مرة واحدة
    Route::delete('/movies', [MovieController::class, 'destroyAll']);
});
