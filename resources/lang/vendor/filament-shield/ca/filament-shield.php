<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */

    'column.name' => 'Nom',
    'column.guard_name' => 'Guard',
    'column.team' => 'Equip',
    'column.roles' => 'Rols',
    'column.permissions' => 'Permisos',
    'column.updated_at' => 'Actualitzat el',

    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */

    'field.name' => 'Nom',
    'field.guard_name' => 'Guard',
    'field.permissions' => 'Permisos',
    'field.team' => 'Equip',
    'field.team.placeholder' => 'Seleccioneu un equip ...',
    'field.select_all.name' => 'Seleccionar tots',
    'field.select_all.message' => 'Activa/desactiva tots els permisos per a aquest rol',

    /*
    |--------------------------------------------------------------------------
    | Navigation & Resource
    |--------------------------------------------------------------------------
    */

    'nav.group' => 'Filament Shield',
    'nav.role.label' => 'Rols',
    'nav.role.icon' => 'heroicon-o-shield-check',
    'resource.label.role' => 'Rol',
    'resource.label.roles' => 'Rols',

    /*
    |--------------------------------------------------------------------------
    | Section & Tabs
    |--------------------------------------------------------------------------
    */

    'section' => 'Entitats',
    'resources' => 'Recursos',
    'widgets' => 'Widgets',
    'pages' => 'Pàgines',
    'custom' => 'Permisos personalitzats',

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    'forbidden' => 'No teniu permís per accedir-hi',

    /*
    |--------------------------------------------------------------------------
    | Resource Permissions' Labels
    |--------------------------------------------------------------------------
    */

    'resource_permission_prefixes_labels' => [
        'view' => 'Veure un registre concret',
        'view_any' => 'Veure el llistat de registres',
        'create' => 'Crear',
        'update' => 'Actualitzar',
        'delete' => 'Eliminar un registre concret',
        'delete_any' => 'Eliminar diversos registres alhora',
        'force_delete' => 'Forçar eliminació d\'un registre concret',
        'force_delete_any' => 'Forçar eliminació de diversos registres',
        'restore' => 'Restaurar un registre concret',
        'reorder' => 'Reordenar',
        'restore_any' => 'Restaurar diversos registres',
        'replicate' => 'Replicar',
    ],
];
