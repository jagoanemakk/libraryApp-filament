<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Loans;
use App\Models\Books;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::query()->where('name', '!=', 'Admin')->count())
                ->description('32k increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Total Loans', Loans::query()->count())
                ->description('7% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Books', Books::query()->count())
                ->description('7% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }

    public static function canView(): bool
    {
        $authUser = auth()->user()->roles->pluck('name')->first();

        if ($authUser == 'Super Admin') {
            return true;
        }

        return false;
    }
}
