<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Migrations\Migrator;

ini_set('display_errors', '0');

try {
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
        ],
    ]);

    if (@file_get_contents('http://127.0.0.1:8080/up', false, $context) === false) {
        exit(1);
    }

    require '/var/www/html/vendor/autoload.php';
    $app = require '/var/www/html/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    $connection = $app->make('db')->connection();

    if ($connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'pgsql') {
        exit(1);
    }

    $connection->select('select 1');

    $migrator = $app->make(Migrator::class);

    if (! $migrator->repositoryExists()) {
        exit(1);
    }

    $migrationFiles = $migrator->getMigrationFiles([
        ...$migrator->paths(),
        $app->databasePath('migrations'),
    ]);
    $ranMigrations = $migrator->getRepository()->getRan();

    if (array_diff(array_keys($migrationFiles), $ranMigrations) !== []) {
        exit(1);
    }
} catch (Throwable) {
    exit(1);
}

exit(0);
