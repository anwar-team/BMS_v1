<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\Book;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): \Filament\Forms\Form
    {
        return $form->schema([
            Wizard::make([
                Step::make('معلومات الكتاب')->schema([
                    TextInput::make('title')->label('عنوان الكتاب')->required(),
                    Textarea::make('description')->label('وصف الكتاب'),
                    FileUpload::make('cover_image')->label('صورة الغلاف'),
                    TextInput::make('published_year')->label('سنة النشر'),
                    TextInput::make('publisher')->label('الناشر'),
                ]),

                Step::make('التصنيفات والمؤلفين')->schema([
                    Select::make('book_section_id')
                        ->relationship('bookSection', 'name')
                        ->label('قسم الكتاب')
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('name')->label('اسم القسم')->required(),
                            Textarea::make('description')->label('وصف القسم'),
                        ])
                        ->required(),

                    Select::make('authors')
                        ->multiple()
                        ->relationship('authors', 'full_name')
                        ->label('المؤلفون')
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('fname')->label('الاسم الأول')->required(),
                            TextInput::make('lname')->label('الكنية')->required(),
                            TextInput::make('mname')->label('اسم الأب'),
                            Textarea::make('biography')->label('السيرة الذاتية'),
                        ])
                        ->required(),
                ]),

                Step::make('المجلدات والفصول')->schema([
                    Repeater::make('volumes')
                        ->label('المجلدات')
                        ->relationship('volumes')
                        ->schema([
                            TextInput::make('number')->label('رقم المجلد')->required(),
                            TextInput::make('title')->label('عنوان المجلد'),

                            Repeater::make('chapters')
                                ->label('فصول هذا المجلد')
                                ->relationship('chapters')
                                ->schema([
                                    TextInput::make('chapter_number')->label('رقم الفصل'),
                                    TextInput::make('title')->label('عنوان الفصل')->required(),
                                ]),
                        ])
                        ->collapsible()
                        ->defaultItems(1),
                ]),

                Step::make('الصفحات')->schema([
                    Repeater::make('pages')
                        ->label('صفحات الكتاب')
                        ->relationship('pages')
                        ->schema([
                            TextInput::make('page_number')->label('رقم الصفحة')->required(),
                            RichEditor::make('content')->label('محتوى الصفحة')->required(),
                        ])
                        ->collapsible()
                        ->defaultItems(1),
                ]),
            ])->columnSpanFull()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable()->label('العنوان'),
            Tables\Columns\TextColumn::make('published_year')->label('السنة'),
        ])->filters([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}