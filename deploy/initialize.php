<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Run after migrations. A routine restart must never replace an existing dataset.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

if (DB::table('datasets')->exists()) {
    fwrite(STDOUT, "Existing dataset preserved.\n");
    exit(0);
}

$manifest = json_decode(file_get_contents(__DIR__.'/../data/manifest.json'), true, flags: JSON_THROW_ON_ERROR);
$actual = hash_file('sha256', __DIR__.'/../data/atlas_municipios.csv');
if (! hash_equals($manifest['output']['sha256'], $actual)) {
    throw new RuntimeException('Atlas CSV checksum differs from the manifest.');
}
$status = $kernel->call('atlas:import', ['--no-interaction' => true]);
fwrite(STDOUT, $kernel->output());
exit($status);
