<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoansResource\Pages;
use App\Filament\Resources\LoansResource\RelationManagers;
use App\Models\Loans;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoansResource extends Resource
{
    protected static ?string $model = Loans::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = "Transaction";

    public function getDueDateStatusAttribute()
    {
        // $today = Carbon::today();
        // $dueDate = $this->due_date;

        // if ($dueDate >= $today) {
        //     $daysLeft = $dueDate->diffInDays($today);
        //     return "{$daysLeft} Hari";
        // } else {
        //     return "Lewat jatuh tempo";
        // }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('books_id')
                    ->relationship('books', 'name')
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('due_date')
                    ->required(),
                Forms\Components\TextInput::make('loan_status')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        $userRoles = auth()->user()->roles->pluck('name')->first();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->visible($userRoles == 'Super Admin'),
                Tables\Columns\TextColumn::make('books.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('loan_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Today' => 'warning',
                        'Expired' => 'danger'
                    })
                    ->label('Status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {

        $userRole = auth()->user()->roles->pluck('name')->first();

        if ($userRole == 'Member') {
            return parent::getEloquentQuery()->where('user_id', auth()->user()->id);
        } else {
            return parent::getEloquentQuery();
        }
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoans::route('/create'),
            'edit' => Pages\EditLoans::route('/{record}/edit'),
        ];
    }
}
