<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoansResource\Pages;
use App\Filament\Resources\LoansResource\RelationManagers;
use App\Models\Books;
use App\Models\Loans;
use App\Models\Monetary;
use Exception;
use Illuminate\Support\Facades\DB;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LoansResource extends Resource
{
    protected static ?string $model = Loans::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = "Transaction";

    public static function form(Form $form): Form
    {
        return $form
            ->schema(static::getFormsComponents())
            ->columns([
                'md' => 1,
                'lg' => 3
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
                        'Expired' => 'danger',
                        'Paid' => 'success'
                    })
                    ->label('Status'),
                Tables\Columns\TextColumn::make('monetaries.fee')
                    ->money('IDR')
                    ->label('Due Charge'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Confirm Return')
                        ->icon('heroicon-s-inbox-arrow-down')
                        ->modalHeading('Confirm Return')
                        ->modalDescription('Are you sure want to confirm this return ?')
                        ->modalWidth(MaxWidth::Medium)
                        ->hidden(fn (Loans $loans) => $loans->deletes_by != NULL)
                        ->action(
                            function (Loans $loans, Books $books): void {

                                DB::beginTransaction();

                                try {
                                    $books->qty += 1;
                                    $books->update();

                                    $loans->monetaries->status = 'Return';
                                    $loans->monetaries->update();

                                    $loans->loan_status = 'Paid';
                                    $loans->deletes_by = auth()->user()->name;
                                    $loans->update();

                                    DB::commit();
                                    // $this->info('Return successfully.');

                                    Notification::make()
                                        ->title('Return Succesfull')
                                        ->success()
                                        ->send();
                                } catch (Exception $e) {
                                    DB::rollBack();
                                    $e->getMessage();
                                    // $this->error('An error occurred: ' . $e->getMessage());
                                }
                            }
                        ),
                    Tables\Actions\EditAction::make(),
                ])
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

    public static function getFormsComponents(): array
    {
        return [
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Select::make('books_id')
                        ->label('Books Title')
                        ->relationship('books', 'name')
                        ->preload()
                        ->native(false)
                        ->placeholder('Select book')
                        ->required(),
                    Forms\Components\DatePicker::make('due_date')
                        ->label('Return Date')
                        ->required(),
                    Forms\Components\Select::make('loan_status')
                        ->label('Status')
                        ->visible(fn ($livewire): bool => $livewire instanceof EditRecord)
                        ->options([
                            'Expired' => 'Expired',
                            'Today' => 'Today',
                        ])
                        ->native(false),
                    Forms\Components\Placeholder::make('fee')
                        ->label('Due Charge')
                        ->content(fn (Loans $loans): ?string => $loans->monetaries->fee),
                ])
        ];
    }
}
