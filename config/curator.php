<?php

declare(strict_types=1);
use Awcodes\Curator\Enums\PreviewableExtensions;
use Awcodes\Curator\Models\Media;
use Awcodes\Curator\Providers\GlideUrlProvider;
use AGC\Filament\Resources\Curator\CustomMediaResource;
use AGC\Filament\Resources\Curator\Pages\CustomCreateMedia;
use AGC\Filament\Resources\Curator\Pages\CustomEditMedia;
use AGC\Filament\Resources\Curator\Pages\CustomListMedia;
use Awcodes\Curator\Resources\Media\Schemas\MediaForm;
use Awcodes\Curator\Resources\Media\Tables\MediaTable;

return [
    'curation_formats' => PreviewableExtensions::toArray(),
    'default_disk' => env('CURATOR_DEFAULT_DISK', 'public'),
    'default_directory' => null,
    'default_visibility' => 'public',
    'features' => [
        'curations' => true,
        'file_swap' => true,
        'directory_restriction' => false,
        'preserve_file_names' => false,
        'tenancy' => [
            'enabled' => false,
            'relationship_name' => null,
        ],
    ],
    'glide_token' => env('CURATOR_GLIDE_TOKEN'),
    'model' => Media::class,
    'path_generator' => null,
    'resource' => [
        'label' => 'Media',
        'plural_label' => 'Media',
        'default_layout' => 'grid',
        'navigation' => [
            'group' => null,
            'icon' => 'heroicon-o-photo',
            'sort' => null,
            'should_register' => true,
            'should_show_badge' => false,
        ],
        'resource' => \AGC\Filament\Resources\Curator\CustomMediaResource::class,
        'pages' => [
            'create' => \AGC\Filament\Resources\Curator\Pages\CustomCreateMedia::class,
            'edit' => \AGC\Filament\Resources\Curator\Pages\CustomEditMedia::class,
            'index' => CustomListMedia::class,
        ],
        'schemas' => [
            'form' => \AGC\Filament\Resources\Curator\CustomMediaForm::class,
        ],
        'tables' => [
            'table' => \AGC\Filament\Resources\Curator\CustomMediaTable::class,
        ],
    ],
    'url_provider' => GlideUrlProvider::class,
];
