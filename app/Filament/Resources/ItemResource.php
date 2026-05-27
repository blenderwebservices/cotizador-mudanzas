<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Artículos';
    protected static ?string $modelLabel = 'Artículo';
    protected static ?string $pluralModelLabel = 'Artículos';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información General')
                ->schema([
                    Forms\Components\TextInput::make('nombre')
                        ->label('Nombre del artículo')
                        ->required(),
                    Forms\Components\TextInput::make('icon')
                        ->label('Ícono (emoji)')
                        ->placeholder('🛏️'),
                    Forms\Components\Select::make('nivel_riesgo')
                        ->label('Nivel de Riesgo')
                        ->options(['bajo' => '🟢 Bajo', 'medio' => '🟡 Medio', 'alto' => '🔴 Alto'])
                        ->required()
                        ->default('bajo'),
                ])->columns(3),

            Forms\Components\Section::make('Propiedades Físicas y Costos')
                ->schema([
                    Forms\Components\TextInput::make('tamano_volumetrico')
                        ->label('Tamaño volumétrico (m³)')
                        ->numeric()->required()->default(0),
                    Forms\Components\TextInput::make('costo_empaque')
                        ->label('Costo de empaque (MXN)')
                        ->numeric()->prefix('$')->required()->default(0),
                    Forms\Components\TextInput::make('tiempo_empaque')
                        ->label('Tiempo de empaque (min)')
                        ->numeric()->suffix('min')->required()->default(0),
                    Forms\Components\TextInput::make('cantidad')
                        ->label('Cantidad por defecto')
                        ->numeric()->required()->default(1),
                ])->columns(4),

            Forms\Components\Section::make('Opciones')
                ->schema([
                    Forms\Components\Toggle::make('requiere_desarmarse')
                        ->label('¿Requiere desarmarse?'),
                    Forms\Components\Toggle::make('activo')
                        ->label('Activo (visible en el cotizador)')
                        ->default(true),
                    Forms\Components\Toggle::make('permite_detalles_opcionales')
                        ->label('Permite detalles opcionales (ej: TV, Refri)'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('icon')
                ->label('')
                ->width('48px'),
            Tables\Columns\TextColumn::make('nombre')
                ->label('Artículo')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('tamano_volumetrico')
                ->label('Volumen (m³)')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('costo_empaque')
                ->label('Costo Empaque')
                ->money('MXN')
                ->sortable(),
            Tables\Columns\TextColumn::make('tiempo_empaque')
                ->label('Tiempo (min)')
                ->suffix(' min')
                ->sortable(),
            Tables\Columns\TextColumn::make('nivel_riesgo')
                ->label('Riesgo')
                ->badge()
                ->color(fn (string $state): string => match($state) {
                    'alto' => 'danger',
                    'medio' => 'warning',
                    default => 'success',
                }),
            Tables\Columns\IconColumn::make('requiere_desarmarse')
                ->label('Desarmar')
                ->boolean(),
            Tables\Columns\IconColumn::make('permite_detalles_opcionales')
                ->label('Detalles')
                ->boolean(),
            Tables\Columns\IconColumn::make('activo')
                ->label('Activo')
                ->boolean(),
        ])
        ->filters([
            Tables\Filters\TernaryFilter::make('activo')->label('Activos'),
        ])
        ->actions([Tables\Actions\EditAction::make()])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
