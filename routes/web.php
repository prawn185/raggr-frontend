<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\IntegrationsController;





Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::group(['prefix' => 'documents'], function () {
        Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/', [DocumentController::class, 'store'])->name('documents.store');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });
    Route::group(['prefix' => 'chats'], function () {
        Route::get('/', [ChatController::class, 'index'])->name('chats.index');
        Route::get('/retrieve', [ChatController::class, 'retrieveChats'])->name('chats.retrieve');
        Route::post('/', [ChatController::class, 'store'])->name('chats.store');
        Route::put('/{chat}', [ChatController::class, 'update'])->name('chats.update');
        Route::delete('/{chat}', [ChatController::class, 'destroy'])->name('chats.destroy');
        
        Route::group(['prefix' => '{chat}'], function () {
            Route::get('/messages', [MessageController::class, 'index'])->name('chats.messages.index');
            Route::post('/messages', [MessageController::class, 'store'])->name('chats.messages.store');
            Route::get('/messages/{message}', [MessageController::class, 'show'])->name('chats.messages.show');
            Route::put('/messages/{message}', [MessageController::class, 'update'])->name('chats.messages.update');
            Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('chats.messages.destroy');
        });
    });
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/process-image', [ImageProcessingController::class, 'processImage'])->name('process-image');

    Route::get('/integrations', [IntegrationsController::class, 'index'])->name('integrations.index');
    Route::post('/integrations/github', [IntegrationsController::class, 'integrateGitHub'])->name('integrations.github');
});

require __DIR__.'/auth.php';

Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');


