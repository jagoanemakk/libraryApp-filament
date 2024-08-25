<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BooksResource\Pages;
use App\Filament\Resources\BooksResource\RelationManagers;
use App\Models\Books;
use App\Models\Loans;
use App\Models\Tags;
use App\Models\User;
use Carbon\Carbon;
// use Tables\Actions\Action;
use Tables\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieTagsEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Tags\Tag;

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
                                        TextEntry::make('qty')
                                            ->label('Status')
                                            ->formatStateUsing(function ($state) {
                                                return $state > 0 ? 'Available' : 'Not Available';
                                            })
                                            ->badge()
                                            ->color(fn ($state) => $state > 0 ? 'success' : 'warning')
                                    ]),
                                    Group::make([
                                        TextEntry::make('categories.name'),
                                        TextEntry::make('qty')
                                            ->label('Quantity')
                                            ->badge()
                                            ->color(fn ($state) => $state > 0 ? 'success' : 'warning'),
                                        TextEntry::make('tags')
                                            ->badge()
                                            ->color('success')
                                            ->separator(',')
                                            ->icon('heroicon-s-hashtag')
                                            ->iconPosition(IconPosition::Before)
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
                Forms\Components\TagsInput::make('tags')
                    ->label('Tags')
                    ->separator(',')
                    ->required(),
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
                Tables\Columns\TextColumn::make('qty')
                    ->label('Quantity')
                    ->formatStateUsing(function ($state) {
                        return $state > 0 ? $state : 'Not Available';
                    })
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'warning')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Get Loans')
                        ->icon('heroicon-o-book-open')
                        ->modalHeading('Return date')
                        ->modalDescription('Maximum period of loan is 14 days')
                        ->modalWidth(MaxWidth::Medium)
                        ->mountUsing(fn (Forms\ComponentContainer $form, Loans $loans) => $form->fill([
                            'due_date' => $loans->due_date
                        ]))
                        ->form([
                            Forms\Components\DatePicker::make('due_date')
                                ->label('Select Date')
                                ->format('Y-m-d')
                                ->native(false)
                                ->suffixIcon('heroicon-m-calendar-date-range')
                                ->closeOnDateSelection()
                                ->minDate(today())
                                ->maxDate(today()->addDays(14))
                                ->required()
                        ])
                        ->action(
                            function (Loans $loans, Books $books, array $data): void {
                                $totalLoans = Loans::where('user_id', auth()->user()->id)->count();

                                $userRole = auth()->user()->roles->pluck("name")->first();

                                if ($userRole == 'Member' && $totalLoans == 2) {

                                    Notification::make()
                                        ->title("You have reach limit of loans")
                                        ->warning()
                                        ->send();
                                } else if ($books->qty <= 0) {
                                    Notification::make()
                                        ->title("Not Available")
                                        ->warning()
                                        ->send();
                                } else {
                                    $loans->user_id = auth()->user()->id;
                                    $loans->books_id = $books->id;
                                    $loans->fill($data);
                                    // $loans->due_date = $loans->due_date->associate($data['due_date'])->format('d-m-Y');
                                    $loans->save();

                                    $books->qty -= 1;
                                    $books->save();

                                    Notification::make()
                                        ->title('Loans Succesfull')
                                        ->success()
                                        ->send();
                                }
                            }
                        ),
                    Tables\Actions\EditAction::make(),
                    // Tables\Actions\DeleteAction::make(),
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
