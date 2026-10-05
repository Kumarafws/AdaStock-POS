<?php

/**
 * Vercel Serverless Function Handler for Laravel
 *
 * This file acts as the serverless bridge connecting Vercel requests
 * into Laravel's public/index.php kernel while ensuring temporary storage
 * directories exist on read-only serverless filesystems.
 */

if (getenv('VERCEL') || getenv('AWS_LAMBDA_FUNCTION_NAME')) {
    $storagePath = getenv('APP_STORAGE_PATH') ?: '/tmp/storage';

    $dirs = [
        $storagePath,
        $storagePath . '/app',
        $storagePath . '/app/public',
        $storagePath . '/framework',
        $storagePath . '/framework/cache',
        $storagePath . '/framework/cache/data',
        $storagePath . '/framework/sessions',
        $storagePath . '/framework/views',
        $storagePath . '/logs',
        '/tmp/bootstrap/cache',
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}

// Forward to public front controller
require __DIR__ . '/../public/index.php';
