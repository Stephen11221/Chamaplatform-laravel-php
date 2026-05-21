<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContributionsController;
use App\Http\Controllers\LoansController;
use App\Http\Controllers\MeetingsController;
use App\Http\Controllers\InvestmentsController;
use App\Http\Controllers\PaymentsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/members', [MembersController::class, 'index'])->name('members');
    Route::post('/members', [MembersController::class, 'store'])->name('members.store');
    Route::patch('/members/{member}', [MembersController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [MembersController::class, 'destroy'])->name('members.destroy');
    Route::post('/members/sms', [MembersController::class, 'sendSms'])->name('members.sms');
    Route::get('/contributions', [ContributionsController::class, 'index'])->name('contributions');
    Route::post('/contributions', [ContributionsController::class, 'store'])->name('contributions.store');
    Route::get('/loans', [LoansController::class, 'index'])->name('loans');
    Route::post('/loans', [LoansController::class, 'store'])->name('loans.store');
    Route::post('/loans/{loan}/approve', [LoansController::class, 'approve'])->name('loans.approve');
    Route::post('/loan-repayments/{repayment}/verify', [LoansController::class, 'verifyRepayment'])->name('loan-repayments.verify');
    Route::get('/investments', [InvestmentsController::class, 'index'])->name('investments');
    Route::post('/investments', [InvestmentsController::class, 'store'])->name('investments.store');
    Route::patch('/investments/{investment}', [InvestmentsController::class, 'update'])->name('investments.update');
    Route::delete('/investments/{investment}', [InvestmentsController::class, 'destroy'])->name('investments.destroy');
    Route::post('/investments/{investment}/vote', [InvestmentsController::class, 'vote'])->name('investments.vote');
    Route::view('/reports', 'pages.reports.index')->name('reports');
    Route::get('/meetings', [MeetingsController::class, 'index'])->name('meetings');
    Route::post('/meetings', [MeetingsController::class, 'store'])->name('meetings.store');
    Route::post('/meetings/{meeting}/reply', [MeetingsController::class, 'reply'])->name('meetings.reply');
    Route::get('/payments', [PaymentsController::class, 'index'])->name('payments');
    Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store');
    Route::post('/payments/mpesa-paybill', [PaymentsController::class, 'updateMpesaPaybill'])->name('payments.update-mpesa-paybill');
    Route::get('/payments/manage', [PaymentsController::class, 'manage'])->name('payments.manage');
    Route::get('/payments/{payment}/edit', [PaymentsController::class, 'edit'])->name('payments.edit');
    Route::patch('/payments/{payment}', [PaymentsController::class, 'update'])->name('payments.update');
    Route::view('/settings', 'pages.settings.index')->name('settings');
    Route::view('/notifications', 'pages.notifications.index')->name('notifications');
    Route::get('/audit', [AuditController::class, 'index'])->name('audit');
    Route::post('/audit', [AuditController::class, 'store'])->name('audit.store');

    Route::view('/profile', 'pages.profile.index')->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
