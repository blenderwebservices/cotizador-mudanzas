<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Models\Agent;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Cotizaciones';
    protected static ?string $modelLabel = 'Cotización';
    protected static ?string $pluralModelLabel = 'Cotizaciones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del Cliente')
                ->schema([
                    Forms\Components\TextInput::make('nombre_cliente')->label('Nombre')->disabled(),
                    Forms\Components\TextInput::make('email_cliente')->label('Email')->disabled(),
                    Forms\Components\TextInput::make('telefono_cliente')->label('Teléfono')->disabled(),
                    Forms\Components\Textarea::make('origen')->label('Origen')->disabled()->columnSpanFull(),
                    Forms\Components\Textarea::make('destino')->label('Destino')->disabled()->columnSpanFull(),
                ])->columns(3),

            Forms\Components\Section::make('Asignación de Agente')
                ->schema([
                    Forms\Components\Select::make('agent_id')
                        ->label('Agente Asignado')
                        ->relationship('agent', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Sin asignar'),
                ]),

            Forms\Components\Section::make('Resumen Logístico')
                ->schema([
                    Forms\Components\TextInput::make('distancia_km')->label('Distancia (km)')->disabled(),
                    Forms\Components\TextInput::make('tiempo_traslado_horas')->label('Tiempo traslado (hrs)')->disabled(),
                    Forms\Components\TextInput::make('volumen_total_m3')->label('Volumen total (m³)')->disabled(),
                    Forms\Components\TextInput::make('personas_sugeridas')->label('Personas sugeridas')->disabled(),
                    Forms\Components\Select::make('vehiculo_sugerido_id')
                        ->label('Vehículo sugerido')
                        ->relationship('vehiculoSugerido', 'nombre')
                        ->disabled(),
                ])->columns(3),

            Forms\Components\Section::make('Desglose Financiero')
                ->schema([
                    Forms\Components\TextInput::make('material_empaque_costo')->label('Material de empaque')->prefix('$')->disabled(),
                    Forms\Components\TextInput::make('costo_combustible')->label('Combustible')->prefix('$')->disabled(),
                    Forms\Components\TextInput::make('comida_trabajadores_costo')->label('Comida trabajadores')->prefix('$')->disabled(),
                    Forms\Components\TextInput::make('salarios_costo')->label('Salarios')->prefix('$')->disabled(),
                    Forms\Components\TextInput::make('gastos_totales')->label('Gastos Totales')->prefix('$')->disabled(),
                    Forms\Components\TextInput::make('ganancia_estimada')->label('Ganancia estimada')->prefix('$')->disabled(),
                    Forms\Components\TextInput::make('precio_sugerido')
                        ->label('PRECIO SUGERIDO')
                        ->prefix('$')
                        ->disabled()
                        ->extraAttributes(['class' => 'font-bold text-primary-600']),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('#')
                ->prefix('MDG-')
                ->formatStateUsing(fn ($state) => 'MDG-' . str_pad($state, 5, '0', STR_PAD_LEFT))
                ->sortable(),
            Tables\Columns\TextColumn::make('nombre_cliente')
                ->label('Cliente')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('email_cliente')
                ->label('Email')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('volumen_total_m3')
                ->label('Volumen (m³)')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('vehiculoSugerido.nombre')
                ->label('Vehículo')
                ->default('Sin asignar'),
            Tables\Columns\TextColumn::make('personas_sugeridas')
                ->label('Personas')
                ->numeric()
                ->sortable(),
            Tables\Columns\TextColumn::make('precio_sugerido')
                ->label('Precio sugerido')
                ->money('MXN')
                ->sortable(),
            Tables\Columns\TextColumn::make('agent.name')
                ->label('Agente')
                ->default('Sin asignar')
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ])
        ->defaultSort('created_at', 'desc')
        ->filters([])
        ->actions([Tables\Actions\EditAction::make()->label('Ver / Editar')])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
