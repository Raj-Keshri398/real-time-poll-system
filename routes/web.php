<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PollController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Default Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/home', function () {
    return redirect('/polls');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Login / Register)
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| POLL ROUTES (User must be logged in to VIEW polls)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Poll list page
    Route::get('/polls', [PollController::class, 'index']);

    // Load single poll (AJAX)
    Route::get('/poll/{id}', [PollController::class, 'show']);

    // ADMIN: Active IP list for poll
    Route::get('/admin/poll/{id}/ips', [PollController::class, 'ipList']);

    // ADMIN: Release IP (allow re-vote)
    Route::post('/admin/release-ip', [PollController::class, 'releaseIp']);
});

/*
|--------------------------------------------------------------------------
| VOTE & RESULTS ROUTES
| (INTENTIONALLY OUTSIDE auth middleware)
|--------------------------------------------------------------------------
| Reason:
| - Voting is IP based
| - AJAX + CSRF only
| - Prevents session/auth mismatch bugs
|--------------------------------------------------------------------------
*/

// Submit vote
Route::post('/vote', [PollController::class, 'vote']);

// Live results
Route::get('/poll-results/{id}', [PollController::class, 'results']);
