<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('descargar_excel')
                ->label('Descargar Excel (Admin)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn (): string => route('quotes.excel.admin', ['quoteId' => $this->record->id]))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
