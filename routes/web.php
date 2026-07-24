<?php

use App\Http\Controllers\ConsoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('console')->name('console.')->middleware('auth')->group(function () {
    Route::get('/', [ConsoleController::class, 'index'])->name('index');
    Route::get('/state', [ConsoleController::class, 'state'])->name('state');

    // Write endpoints are rate limited to curb abuse of the Cursor agent.
    Route::middleware('throttle:cursor')->group(function () {
        Route::post('/tasks', [ConsoleController::class, 'storeTask'])->name('tasks.store');
        Route::post('/tasks/{task}/run', [ConsoleController::class, 'runTask'])->name('tasks.run');
        Route::post('/tasks/{task}/cancel', [ConsoleController::class, 'cancelTask'])->name('tasks.cancel');
        Route::delete('/tasks/{task}', [ConsoleController::class, 'destroyTask'])->name('tasks.destroy');
        Route::post('/chat', [ConsoleController::class, 'chat'])->name('chat');
        Route::post('/chat/clear', [ConsoleController::class, 'clearChat'])->name('chat.clear');
    });
});
