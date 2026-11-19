<?php

// Mounted only by the isolated proxy harness, never into a deployed app.
header('Content-Type: application/json');
echo json_encode([
    'client' => $_SERVER['REMOTE_ADDR'],
    'https' => $_SERVER['HTTPS'],
    'scheme' => $_SERVER['REQUEST_SCHEME'],
    'port' => $_SERVER['SERVER_PORT'],
    'xff' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
    'forwarded_proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '',
], JSON_THROW_ON_ERROR);
