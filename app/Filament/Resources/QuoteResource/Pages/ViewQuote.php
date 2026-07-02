<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewQuote extends ViewRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('descargar_pdf_cliente')
                ->label('Descargar PDF (Cliente)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->url(fn (): string => route('quotes.pdf.client', ['quoteId' => $this->record->id]))
                ->openUrlInNewTab(),
            Actions\Action::make('descargar_excel')
                ->label('Descargar Excel (Admin)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn (): string => route('quotes.excel.admin', ['quoteId' => $this->record->id]))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
