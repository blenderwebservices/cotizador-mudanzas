<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuoteController;

Route::get('/', function () {
    return view('cotizador');
});

Route::get('/api/items', [QuoteController::class, 'getItems']);
Route::post('/api/quotes', [QuoteController::class, 'store']);

// PDF generation routes (existing)
Route::get('quotes/{quoteId}/pdf/client', [QuoteController::class, 'clientPdf'])->name('quotes.pdf.client');
Route::get('quotes/{quoteId}/pdf/admin', [QuoteController::class, 'adminPdf'])->name('quotes.pdf.admin');

// Legacy routes for compatibility
Route::get('quotes/{quote}/pdf/client', [QuoteController::class, 'clientPdf'])->name('quotes.pdf.client.legacy');
Route::get('quotes/{quote}/pdf/admin', [QuoteController::class, 'adminPdf'])->name('quotes.pdf.admin.legacy');
