<?php

declare(strict_types=1);

// Use the existing image's CacheTool to inspect FPM's shared cache, not CLI OPcache.
try {
    Phar::loadPhar('/usr/local/bin/cachetool', 'preload-diagnostic.phar');
    require 'phar://preload-diagnostic.phar/vendor/autoload.php';

    $cacheTool = \CacheTool\CacheTool::factory(
        new \CacheTool\Adapter\FastCGI('127.0.0.1:9000'),
        '/tmp',
        new \Psr\Log\NullLogger(),
    );
    $result = $cacheTool->getAdapter()->run(\CacheTool\Code::fromString(<<<'PHP'
        if (PHP_SAPI !== 'fpm-fcgi') {
            throw new \RuntimeException('Expected PHP-FPM, got ' . PHP_SAPI);
        }

        $status = function_exists('opcache_get_status') ? opcache_get_status(false) : false;
        $preload = is_array($status) ? ($status['preload_statistics'] ?? []) : [];
        $directives = [];
        foreach (['opcache.enable', 'opcache.preload', 'opcache.preload_user', 'opcache.memory_consumption',
            'opcache.max_accelerated_files', 'opcache.validate_timestamps', 'opcache.restrict_api'] as $name) {
            $directives[$name] = ini_get($name);
        }

        $files = [];
        foreach (array_unique(array_filter([
            ini_get('opcache.preload'),
            '/var/www/html/var/cache/prod/App_KernelProdContainer.preload.php',
        ])) as $path) {
            $exists = is_file($path);
            $files[$path] = [
                'exists_now' => $exists,
                'readable_now' => is_readable($path),
                'modified_at_utc' => $exists ? gmdate('c', filemtime($path)) : null,
            ];
        }

        $preloadedScripts = $preload['scripts'] ?? [];
        return [
            'observed_at_utc' => gmdate('c'),
            'sapi' => PHP_SAPI,
            'php_version' => PHP_VERSION,
            'worker_pid' => getmypid(),
            'directives' => $directives,
            'status_available' => is_array($status),
            'opcache_enabled' => $status['opcache_enabled'] ?? null,
            'cache_full' => $status['cache_full'] ?? null,
            'restart_pending' => $status['restart_pending'] ?? null,
            'restart_in_progress' => $status['restart_in_progress'] ?? null,
            'memory_usage' => $status['memory_usage'] ?? null,
            'opcache_statistics' => $status['opcache_statistics'] ?? null,
            'preload' => [
                'statistics_available' => array_key_exists('preload_statistics', $status ?: []),
                'memory_consumption' => $preload['memory_consumption'] ?? null,
                'script_count' => count($preloadedScripts),
                'class_count' => count($preload['classes'] ?? []),
                'function_count' => count($preload['functions'] ?? []),
                'symfony_preload_script_present' => in_array(
                    '/var/www/html/var/cache/prod/App_KernelProdContainer.preload.php',
                    $preloadedScripts,
                    true,
                ),
            ],
            'files' => $files,
        ];
        PHP));

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    if (!is_array($result) || !($result['status_available'] ?? false)) {
        throw new RuntimeException('FPM OPcache status is unavailable; preload cannot be verified.');
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Preload diagnostic failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
