<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/leads', [\App\Http\Controllers\LeadController::class, 'index'])->name('leads.index');

    Route::get('/lists', [\App\Http\Controllers\ListController::class, 'index'])->name('lists.index');
    Route::get('/lists/create', [\App\Http\Controllers\ListController::class, 'create'])->name('lists.create');
    Route::post('/lists', [\App\Http\Controllers\ListController::class, 'store'])->name('lists.store');

    Route::get('/leads/upload', [\App\Http\Controllers\LeadImportController::class, 'create'])->name('leads.import.create');
    Route::post('/leads/upload', [\App\Http\Controllers\LeadImportController::class, 'preview'])->name('leads.import.preview');
    Route::post('/leads/upload/confirm', [\App\Http\Controllers\LeadImportController::class, 'store'])->name('leads.import.store');

    Route::get('/campaigns', [\App\Http\Controllers\CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/inbound-groups', [\App\Http\Controllers\InboundGroupController::class, 'index'])->name('inbound-groups.index');

    Route::get('/call-logs/incoming', [\App\Http\Controllers\CallLogController::class, 'incoming'])->name('call-logs.incoming');
    Route::get('/call-logs/outgoing', [\App\Http\Controllers\CallLogController::class, 'outgoing'])->name('call-logs.outgoing');

    Route::get('/agents', [\App\Http\Controllers\AgentController::class, 'index'])->name('agents.index');

    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
});

