<?php

declare(strict_types=1);
use AGC\Filament\Resources\Curator\CustomMediaForm;
use AGC\Filament\Resources\Curator\CustomMediaResource;
use AGC\Filament\Resources\Curator\CustomMediaTable;
use AGC\Filament\Resources\Curator\Pages\CustomCreateMedia;
use AGC\Filament\Resources\Curator\Pages\CustomEditMedia;
use AGC\Filament\Resources\Curator\Pages\CustomListMedia;
use Awcodes\Curator\Enums\PreviewableExtensions;
use Awcodes\Curator\Models\Media;
use Awcodes\Curator\Providers\GlideUrlProvider;

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
        'resource' => CustomMediaResource::class,
        'pages' => [
            'create' => CustomCreateMedia::class,
            'edit' => CustomEditMedia::class,
            'index' => CustomListMedia::class,
        ],
        'schemas' => [
            'form' => CustomMediaForm::class,
        ],
        'tables' => [
            'table' => CustomMediaTable::class,
        ],
    ],
    'url_provider' => GlideUrlProvider::class,
];
