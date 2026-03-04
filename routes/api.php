<?php

use App\Http\Controllers\Api\TodoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/hello', function () {
    return response()->json([
        'message' => 'This is a message from Andromeda Galaxy! Destroy Earth in 7 Days.'
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('todos', TodoController::class);
});
