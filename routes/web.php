<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// The library is the app — send the root straight to it rather than
// showing Laravel's default splash page.
Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
