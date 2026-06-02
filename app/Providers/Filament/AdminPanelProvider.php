<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use AGC\Filament\Pages\FooterSettingsPage;
use AGC\Filament\Pages\SocialMediaSettingsPage;
use AGC\Filament\Pages\TrustBarSettingsPage;
use AGC\Filament\Pages\WorkWithUsSettingsPage;
use AGC\Filament\Resources\HomeSectionResource;
use AGC\Filament\Resources\MenuItemResource;
use AGC\Filament\Resources\NewsResource;
use AGC\Filament\Resources\OfficeResource;
use AGC\Filament\Resources\PageResource;
use AGC\Filament\Resources\ServiceResource;
use AGC\Filament\Resources\TeamMemberResource;
use AGC\Filament\Resources\UserResource;
use Awcodes\Curator\CuratorPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\App;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->bootUsing(function (): void {
                App::setLocale('es');
            })
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(
                    '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block" />'
                ),
            )
            ->colors([
                'primary' => Color::hex('#00346f'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->resources([
                HomeSectionResource::class,
                PageResource::class,
                NewsResource::class,
                ServiceResource::class,
                TeamMemberResource::class,
                MenuItemResource::class,
                OfficeResource::class,
                UserResource::class,
            ])
            ->plugins([
                CuratorPlugin::make()
                    ->label('Archivo')
                    ->pluralLabel('Biblioteca de medios')
                    ->navigationIcon('heroicon-o-photo')
                    ->navigationGroup('Contenido')
                    ->navigationSort(10)
                    ->registerNavigation(true),
                FilamentShieldPlugin::make(),
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                FooterSettingsPage::class,
                SocialMediaSettingsPage::class,
                TrustBarSettingsPage::class,
                WorkWithUsSettingsPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
