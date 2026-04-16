<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\TenantThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Public form rendering & submission (no auth)
Route::get('/f/{slug}', [PublicFormController::class, 'show'])->name('public.form.show');
Route::post('/f/{slug}/submit', [PublicFormController::class, 'submit'])->name('public.form.submit');
Route::get('/f/{slug}/thanks', [PublicFormController::class, 'thanks'])->name('public.form.thanks');

// Admin (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/forms',                  [FormController::class, 'index'])->name('forms.index');
    Route::post('/forms',                 [FormController::class, 'store'])->name('forms.store');
    Route::get('/forms/{form}/edit',      [FormController::class, 'edit'])->name('forms.edit');
    Route::put('/forms/{form}',           [FormController::class, 'update'])->name('forms.update');
    Route::delete('/forms/{form}',        [FormController::class, 'destroy'])->name('forms.destroy');
    Route::post('/forms/{form}/publish',  [FormController::class, 'publish'])->name('forms.publish');
    Route::get('/forms/{form}/responses', [FormController::class, 'responses'])->name('forms.responses');
    Route::delete('/forms/{form}/responses/{response}', [FormController::class, 'destroyResponse'])->name('forms.responses.destroy');

    Route::get('/theme',  [TenantThemeController::class, 'edit'])->name('theme.edit');
    Route::put('/theme',  [TenantThemeController::class, 'update'])->name('theme.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
