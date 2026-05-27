<?php

use App\Http\Controllers\Admin\InvoicePdfController;
use App\Http\Controllers\Admin\TicketPdfController;
use App\Http\Controllers\ErpBlueprintController;
use App\Http\Controllers\StartTrialController;
use Illuminate\Support\Facades\Route;

Route::get('/', ErpBlueprintController::class)->name('erp.blueprint');
Route::get('/teste-gratis', [StartTrialController::class, 'create'])->name('trial.create');
Route::post('/iniciar-teste', [StartTrialController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('trial.start');

Route::prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/tickets/{ticket}/pdf', TicketPdfController::class)->name('tickets.pdf');
        Route::get('/invoices/{invoice}/pdf', InvoicePdfController::class)->name('invoices.pdf');
    });
