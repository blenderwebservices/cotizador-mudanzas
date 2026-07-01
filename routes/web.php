<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QuoteController;

Route::get('/', function () {
    return view('cotizador');
});

Route::get('/api/items', [QuoteController::class, 'getItems']);
Route::get('/api/autocomplete', [QuoteController::class, 'autocomplete']);
Route::post('/api/quotes', [QuoteController::class, 'store']);

// PDF generation routes (existing)
Route::get('quotes/{quoteId}/pdf/client', [QuoteController::class, 'clientPdf'])->name('quotes.pdf.client');
Route::get('quotes/{quoteId}/pdf/admin', [QuoteController::class, 'adminPdf'])->name('quotes.pdf.admin');

// Excel generation routes (new)
Route::get('quotes/{quoteId}/excel/admin', [QuoteController::class, 'adminExcel'])->name('quotes.excel.admin');

// Legacy routes for compatibility
Route::get('quotes/{quote}/pdf/client', [QuoteController::class, 'clientPdf'])->name('quotes.pdf.client.legacy');
Route::get('quotes/{quote}/pdf/admin', [QuoteController::class, 'adminPdf'])->name('quotes.pdf.admin.legacy');
Route::get('quotes/{quote}/excel/admin', [QuoteController::class, 'adminExcel'])->name('quotes.excel.admin.legacy');
