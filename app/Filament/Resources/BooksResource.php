<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BooksResource\Pages;
use App\Filament\Resources\BooksResource\RelationManagers;
use App\Models\Books;
use App\Models\Loans;
use App\Models\User;
// use Tables\Actions\Action;
use Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BooksResource extends Resource
{
    protected static ?string $model = Books::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = "Resource";

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('name'),
                                        TextEntry::make('author'),
                                        TextEntry::make('status')
                                            ->badge()
                                            ->color('success')
                                    ]),
                                    Group::make([
                                        TextEntry::make('categories.name'),
                                    ]),
                                ]), ImageEntry::make('image')
                                ->height(200)
                                ->hiddenLabel()
                                ->grow(false)
                        ])->columns([
                            'sm' => 3,
                            'xl' => 6,
                            '2xl' => 8,
                        ])
                    ]),
                Section::make('Description')
                    ->schema([
                        TextEntry::make('description')
                            ->prose()
                            ->markdown()
                            ->hiddenLabel()
                    ])->collapsible()
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('categories_id')
                    ->relationship(name: 'categories', titleAttribute: 'name')
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('author')
                    ->maxLength(255),
                Forms\Components\TextInput::make('status')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(255)
                    ->columnSpan(2)
                    ->rows(5),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('author')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Get Loans')
                        ->icon('heroicon-o-book-open')
                        ->requiresConfirmation()
                        ->modalHeading('Get Loans ?')
                        ->modalDescription('Are you sure you\'d like to loan this books ?')
                        ->modalSubmitActionLabel('Yes, I am sure')
                        ->action(function (Loans $loans, Books $books, User $user) {
                            $totalLoans = Loans::where('user_id', auth()->user()->id)->count();

                            $userRole = auth()->user()->roles->pluck("name")->first();

                            if ($userRole == 'Member' && $totalLoans == 2) {

                                Notification::make()
                                    ->title('Loans failed')
                                    ->warning()
                                    ->send();
                            } else {
                                $loans->user_id = auth()->user()->id;
                                $loans->books_id = $books->id;
                                $loans->due_date = date('Y-m-d H:i:s');
                                $loans->save();

                                Notification::make()
                                    ->title('Loans Succesfull')
                                    ->success()
                                    ->send();
                            }
                        }),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBooks::route('/create'),
            'view' => Pages\ViewBooksDetail::route('/{record}'),
            'edit' => Pages\EditBooks::route('/{record}/edit'),
        ];
    }
}
