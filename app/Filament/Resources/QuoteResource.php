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
                    Forms\Components\TextInput::make('nombre_cliente')->label('Nombre')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\TextInput::make('email_cliente')->label('Email')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\TextInput::make('telefono_cliente')->label('Teléfono')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\Textarea::make('origen')->label('Origen')->disabled(fn (string $operation): bool => $operation === 'view')->columnSpanFull(),
                    Forms\Components\Textarea::make('destino')->label('Destino')->disabled(fn (string $operation): bool => $operation === 'view')->columnSpanFull(),
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
                    Forms\Components\TextInput::make('distancia_km')->label('Distancia (km)')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\TextInput::make('tiempo_traslado_horas')->label('Tiempo traslado (hrs)')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\TextInput::make('volumen_total_m3')->label('Volumen total (m³)')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\TextInput::make('personas_sugeridas')->label('Personas sugeridas')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\Select::make('vehiculo_sugerido_id')
                        ->label('Vehículo sugerido')
                        ->relationship('vehiculoSugerido', 'nombre')
                        ->disabled(fn (string $operation): bool => $operation === 'view'),
                ])->columns(3),

            Forms\Components\Section::make('Complejidad Logística (Drivers ABC)')
                ->schema([
                    Forms\Components\TextInput::make('pisos_origen')->label('Pisos Origen')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\TextInput::make('distancia_caminata_origen_m')->label('Caminata Origen (m)')->suffix('m')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\Placeholder::make('ascensor_origen_placeholder')
                        ->label('Ascensor Origen')
                        ->content(fn ($record) => ($record && $record->detalles_json && ($record->detalles_json['elevatorStart'] ?? 'no') === 'yes') ? 'Sí' : 'No'),

                    Forms\Components\TextInput::make('pisos_destino')->label('Pisos Destino')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\TextInput::make('distancia_caminata_destino_m')->label('Caminata Destino (m)')->suffix('m')->disabled(fn (string $operation): bool => $operation === 'view'),
                    Forms\Components\Toggle::make('ascensor_destino')->label('Ascensor Destino')->disabled(fn (string $operation): bool => $operation === 'view'),
                ])->columns(3),

            Forms\Components\Section::make('Desglose de Actividades ABC')
                ->schema([
                    Forms\Components\TextInput::make('costo_actividad_comercial')
                        ->label('Actividad A: Comercial y Planificación')
                        ->prefix('$')->disabled(fn (string $operation): bool => $operation === 'view'),
                    
                    Forms\Components\TextInput::make('costo_actividad_embalaje')
                        ->label('Actividad B: Embalaje y Preparación')
                        ->prefix('$')->disabled(fn (string $operation): bool => $operation === 'view'),
                    
                    Forms\Components\TextInput::make('costo_actividad_carga')
                        ->label('Actividad C: Carga y Estiba')
                        ->prefix('$')->disabled(fn (string $operation): bool => $operation === 'view'),
                    
                    Forms\Components\TextInput::make('costo_actividad_transporte')
                        ->label('Actividad D: Transporte (Conducción)')
                        ->prefix('$')->disabled(fn (string $operation): bool => $operation === 'view'),
                    
                    Forms\Components\TextInput::make('costo_actividad_descarga')
                        ->label('Actividad E: Descarga y Desembalaje')
                        ->prefix('$')->disabled(fn (string $operation): bool => $operation === 'view'),

                    Forms\Components\Placeholder::make('space')->label('')->columnSpan(1),

                    Forms\Components\TextInput::make('gastos_totales')
                        ->label('Costo Operativo Total (Gastos)')
                        ->prefix('$')->disabled(fn (string $operation): bool => $operation === 'view'),
                    
                    Forms\Components\TextInput::make('ganancia_estimada')
                        ->label('Ganancia Estimada')
                        ->prefix('$')->disabled(fn (string $operation): bool => $operation === 'view'),
                    
                    Forms\Components\TextInput::make('precio_sugerido')
                        ->label('PRECIO SUGERIDO')
                        ->prefix('$')
                        ->disabled(fn (string $operation): bool => $operation === 'view')
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
        ->actions([
            Tables\Actions\ViewAction::make()->label('Ver'),
            Tables\Actions\EditAction::make()->label('Editar'),
            Tables\Actions\Action::make('descargar_pdf_cliente')
                ->label('PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->url(fn (Quote $record): string => route('quotes.pdf.client', ['quoteId' => $record->id]))
                ->openUrlInNewTab(),
            Tables\Actions\Action::make('descargar_excel')
                ->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn (Quote $record): string => route('quotes.excel.admin', ['quoteId' => $record->id]))
                ->openUrlInNewTab(),
        ])
        ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'view' => Pages\ViewQuote::route('/{record}'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
