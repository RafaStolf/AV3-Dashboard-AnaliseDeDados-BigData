<?php

// No Laravel bootstrap: this tool also works while the app is stopped.

function verifyDatabase(string $path): array
{
    $db = new SQLite3($path, SQLITE3_OPEN_READONLY);
    $db->enableExceptions(true);
    $db->busyTimeout(30000);
    if ($db->querySingle('PRAGMA integrity_check') !== 'ok') {
        throw new RuntimeException('SQLite integrity_check failed.');
    }
    if ($db->query('PRAGMA foreign_key_check')->fetchArray() !== false) {
        throw new RuntimeException('SQLite foreign_key_check failed.');
    }
    $counts = [];
    foreach (['migrations', 'datasets', 'territories', 'observations'] as $table) {
        $counts[$table] = $db->querySingle('SELECT COUNT(*) FROM '.$table);
        if ($counts[$table] < 1) {
            throw new RuntimeException('Missing or empty table: '.$table);
        }
    }
    $db->close();

    return $counts;
}

function snapshot(string $source, string $target): void
{
    $input = new SQLite3($source, SQLITE3_OPEN_READONLY);
    $input->enableExceptions(true);
    $input->busyTimeout(30000);
    $output = new SQLite3($target, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
    $output->enableExceptions(true);
    $output->busyTimeout(30000);
    if (! $input->backup($output)) {
        throw new RuntimeException('SQLite backup failed.');
    }
    $output->close();
    $input->close();
}

try {
    $mode = $argv[1] ?? '';
    $file = $argv[2] ?? '';
    $database = getenv('DB_DATABASE') ?: '';
    if (! in_array($mode, ['backup', 'verify', 'restore'], true) || $file === '') {
        throw new InvalidArgumentException('Usage: php deploy/sqlite.php backup|verify|restore /absolute/path.sqlite');
    }
    if ($mode === 'verify') {
        echo json_encode(verifyDatabase($file), JSON_THROW_ON_ERROR).PHP_EOL;
        exit(0);
    }
    if (! str_starts_with($database, '/var/www/html/storage/') || ! is_file($database)) {
        throw new RuntimeException('DB_DATABASE must identify an existing database in the storage volume.');
    }
    if ($file[0] !== '/' || realpath($file) === realpath($database)) {
        throw new InvalidArgumentException('Use an absolute backup path different from DB_DATABASE.');
    }
    if ($mode === 'backup') {
        if (file_exists($file)) {
            throw new RuntimeException('Refusing to overwrite a backup.');
        }
        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0700, true);
        }
        snapshot($database, $file);
        chmod($file, 0600);
        echo json_encode(verifyDatabase($file), JSON_THROW_ON_ERROR).PHP_EOL;
    } else {
        if (getenv('RESTORE_OFFLINE') !== 'yes') {
            throw new RuntimeException('Stop nginx and app first, then set RESTORE_OFFLINE=yes.');
        }
        $expected = verifyDatabase($file);
        $temporary = $database.'.restore-'.bin2hex(random_bytes(6));
        snapshot($file, $temporary);
        chmod($temporary, 0600);
        if (verifyDatabase($temporary) !== $expected) {
            throw new RuntimeException('Restored table counts differ from backup.');
        }
        // Safe only with ALL writers stopped; never remove a live WAL.
        foreach (['-wal', '-shm', '-journal'] as $suffix) {
            if (file_exists($database.$suffix) && ! unlink($database.$suffix)) {
                throw new RuntimeException('Cannot remove offline SQLite sidecar.');
            }
        }
        if (! rename($temporary, $database)) {
            throw new RuntimeException('Cannot replace offline database.');
        }
        echo json_encode(verifyDatabase($database), JSON_THROW_ON_ERROR).PHP_EOL;
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage().PHP_EOL);
    exit(1);
}
