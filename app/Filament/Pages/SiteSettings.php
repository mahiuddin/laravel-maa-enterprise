<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class SiteSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Manage Site Settings';

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'hero_title' => DB::table('site_settings')
                ->where('key', 'hero_title')
                ->value('value'),

            'hero_subtitle' => DB::table('site_settings')
                ->where('key', 'hero_subtitle')
                ->value('value'),

            'welcome_text' => DB::table('site_settings')
                ->where('key', 'welcome_text')
                ->value('value'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hero Banner Section')
                    ->description(
                        'This is the main banner at the top of the homepage.'
                    )
                    ->schema([
                        TextInput::make('hero_title')
                            ->label('Main Catchy Heading')
                            ->required(),

                        TextInput::make('hero_subtitle')
                            ->label('Sub-heading / Tagline')
                            ->required(),
                    ]),

                Section::make('Welcome Message')
                    ->schema([
                        RichEditor::make('welcome_text')
                            ->label('Introduction Paragraph')
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Update Site Settings')
                ->action('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'updated_at' => now(),
                ]
            );
        }

        Notification::make()
            ->title('Site settings updated successfully!')
            ->success()
            ->send();
    }
}
