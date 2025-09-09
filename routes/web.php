<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SurgeryController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/login');

Route::get('/dashboard', function (Request $request) {
    return $request->user()->hasRole('medico')
        ? redirect()->route('medico')
        : Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'role:adm|medico|enfermeiro'])->name('dashboard');

Route::middleware(['auth', 'role:adm|medico|enfermeiro'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:adm'])->get('/admin', fn () => 'admin area');
Route::middleware(['auth', 'role:medico'])->get('/medico', [SurgeryController::class, 'index'])->name('medico');
Route::middleware(['auth', 'role:enfermeiro'])->get('/enfermeiro', fn () => Inertia::render('Enfermeiro/Confirm'));
Route::middleware(['auth', 'role:medico'])->post('/surgeries', [SurgeryController::class, 'store'])->name('surgeries.store');
Route::middleware(['auth', 'role:enfermeiro'])->post('/surgeries/{surgery}/confirm', [SurgeryController::class, 'confirm'])->name('surgeries.confirm');

require __DIR__.'/auth.php';
