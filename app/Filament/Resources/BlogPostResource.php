<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use App\Services\SEOService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Blog Posts';

    protected static ?string $modelLabel = 'Blog Post';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Content')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('English')
                            ->schema([
                                Forms\Components\TextInput::make('title.en')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter post title in English')
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $slug = str($state)->slug()->toString();
                                            $set('slug', $slug);
                                        }
                                    }),

                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->unique(BlogPost::class, 'slug', ignoreRecord: true)
                                    ->placeholder('auto-generated from title'),

                                Forms\Components\RichEditor::make('content.en')
                                    ->label('Content')
                                    ->required()
                                    ->columnSpanFull()
                                    ->minLength(200)
                                    ->maxLength(50000),

                                Forms\Components\Textarea::make('description.en')
                                    ->label('Description/Excerpt')
                                    ->required()
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->placeholder('Brief description for previews'),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('العربية')
                            ->schema([
                                Forms\Components\TextInput::make('title.ar')
                                    ->label('العنوان')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('أدخل عنوان المقالة بالعربية'),

                                Forms\Components\TextInput::make('title_ar_slug')
                                    ->label('الرابط (URL Slug)')
                                    ->disabled()
                                    ->dehydrated(false),

                                Forms\Components\RichEditor::make('content.ar')
                                    ->label('المحتوى')
                                    ->required()
                                    ->columnSpanFull()
                                    ->minLength(200)
                                    ->maxLength(50000),

                                Forms\Components\Textarea::make('description.ar')
                                    ->label('الوصف')
                                    ->required()
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->placeholder('وصف موجز للمعاينات'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Section::make('Media & Images')
                    ->description('Upload featured image and OG image for social sharing')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image_url')
                            ->label('Featured Image')
                            ->image()
                            ->directory('blog/featured')
                            ->visibility('public')
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('og_image')
                            ->label('OG Image (Social Media)')
                            ->image()
                            ->directory('blog/og')
                            ->visibility('public')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('SEO Metadata')
                    ->description('Configure SEO settings and metadata')
                    ->icon('heroicon-o-sparkles')
                    ->collapsible()
                    ->collapsed(false)
                    ->schema([
                        Forms\Components\Tabs::make('SEO')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('English SEO')
                                    ->schema([
                                        Forms\Components\TextInput::make('meta_title.en')
                                            ->label('Meta Title')
                                            ->required()
                                            ->maxLength(60)
                                            ->minLength(30)
                                            ->helperText('30-60 characters for optimal SEO (Current: {count})')
                                            ->placeholder('Enter SEO title'),

                                        Forms\Components\Textarea::make('meta_description.en')
                                            ->label('Meta Description')
                                            ->required()
                                            ->maxLength(160)
                                            ->minLength(120)
                                            ->rows(3)
                                            ->helperText('120-160 characters (Current: {count})')
                                            ->placeholder('Enter SEO description'),

                                        Forms\Components\TextInput::make('canonical_url')
                                            ->label('Canonical URL')
                                            ->url()
                                            ->required()
                                            ->placeholder('https://devseo.com/blog/post-slug'),
                                    ]),

                                Forms\Components\Tabs\Tab::make('Arabic SEO')
                                    ->schema([
                                        Forms\Components\TextInput::make('meta_title.ar')
                                            ->label('العنوان الوصفي')
                                            ->required()
                                            ->maxLength(60)
                                            ->minLength(30),

                                        Forms\Components\Textarea::make('meta_description.ar')
                                            ->label('الوصف الوصفي')
                                            ->required()
                                            ->maxLength(160)
                                            ->minLength(120)
                                            ->rows(3),
                                    ]),

                                Forms\Components\Tabs\Tab::make('Schema')
                                    ->schema([
                                        Forms\Components\Placeholder::make('schema_info')
                                            ->label('JSON-LD Schema')
                                            ->content('Schema is automatically generated based on content and will be displayed here after saving.')
                                            ->hidden(fn ($record) => !$record),

                                        Forms\Components\CodeEditor::make('schema_json')
                                            ->label('Custom Schema JSON (Advanced)')
                                            ->language('json')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('SEO Score & Analysis')
                    ->description('Real-time SEO performance analysis')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsible()
                    ->collapsed(false)
                    ->schema([
                        Forms\Components\View::make('filament.components.seo-score-card')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Publishing & Metadata')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->columnSpan(1),

                        Forms\Components\Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),

                        Forms\Components\CheckboxList::make('regions')
                            ->label('Target Regions')
                            ->options([
                                'EG' => 'Egypt',
                                'US' => 'United States',
                                'GLOBAL' => 'Global Audience',
                            ])
                            ->default(['GLOBAL'])
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Relationships')
                    ->schema([
                        Forms\Components\Select::make('categories')
                            ->label('Categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpan(1),

                        Forms\Components\Select::make('tags')
                            ->label('Tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->getStateUsing(fn ($record) => $record->getTranslation('title', 'en'))
                    ->searchable(['title->en', 'title->ar'])
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->copyable()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('seo_score')
                    ->label('SEO Score')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('published')
                    ->label('Published Only')
                    ->query(fn (Builder $query) => $query->where('is_published', true)),

                Tables\Filters\SelectFilter::make('regions')
                    ->multiple()
                    ->options([
                        'EG' => 'Egypt',
                        'US' => 'United States',
                        'GLOBAL' => 'Global',
                    ]),

                Tables\Filters\SelectFilter::make('author_id')
                    ->label('Author')
                    ->relationship('author', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRecordSubNavigation($record): array
    {
        return [
            Pages\EditBlogPost::getUrl($record),
        ];
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
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
