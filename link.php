<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

if (!file_exists(public_path('storage'))) {
    symlink(storage_path('app/public'), public_path('storage'));
    echo "Symlink created successfully!\n";
} else {
    echo "Symlink already exists!\n";
}
