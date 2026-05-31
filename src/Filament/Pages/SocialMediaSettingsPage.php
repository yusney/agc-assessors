<?php

declare(strict_types=1);

namespace AGC\Filament\Pages;

use AGC\Infrastructure\Persistence\Eloquent\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SocialMediaSettingsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-share';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Redes Sociales';
    protected static ?string $title = 'Configuración de Redes Sociales';
    protected string $view = 'filament.pages.social-media-settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'networks' => SiteSetting::get('social_networks', []) ?? [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Redes sociales en el navbar')
                    ->description('Se muestran máximo 3 iconos directamente. Si hay más, aparece un botón "⋯" que despliega el resto. El orden se gestiona arrastrando.')
                    ->schema([
                        Repeater::make('networks')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('platform')
                                    ->label('Red social')
                                    ->required()
                                    ->options([
                                        'linkedin'  => 'LinkedIn',
                                        'twitter'   => 'X (Twitter)',
                                        'instagram' => 'Instagram',
                                        'facebook'  => 'Facebook',
                                        'youtube'   => 'YouTube',
                                        'tiktok'    => 'TikTok',
                                        'whatsapp'  => 'WhatsApp',
                                    ])
                                    ->columnSpan(1),

                                TextInput::make('url')
                                    ->label('URL del perfil')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://www.linkedin.com/company/...')
                                    ->columnSpan(2),

                                Toggle::make('is_active')
                                    ->label('Visible')
                                    ->default(true)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Añadir red social')
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function save(): void
    {
        SiteSetting::set('social_networks', $this->form->getState()['networks'] ?? []);

        Notification::make()
            ->title('Redes sociales guardadas')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar')
                ->submit('save'),
        ];
    }
}
