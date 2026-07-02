<?php

namespace App\Filament\Widgets;

use App\Models\Quote;
use App\Models\Item;
use App\Models\Vehicle;
use App\Models\Agent;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Cotizaciones', Quote::count())
                ->description('Ver listado de cotizaciones')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->url(route('filament.admin.resources.quotes.index')),

            Stat::make('Artículos', Item::count())
                ->description('Administrar catálogo de artículos')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info')
                ->url(route('filament.admin.resources.items.index')),

            Stat::make('Vehículos', Vehicle::count())
                ->description('Gestionar flota de vehículos')
                ->descriptionIcon('heroicon-m-truck')
                ->color('warning')
                ->url(route('filament.admin.resources.vehicles.index')),

            Stat::make('Agentes', Agent::count())
                ->description('Administrar agentes y personal')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('danger')
                ->url(route('filament.admin.resources.agents.index')),
        ];
    }
}
