<?php

declare(strict_types=1);

namespace Tests\App\Performance;

class JmeterCsvReporter
{
    /**
     * @param resource $handle
     */
    public function writeHeader($handle): void
    {
        fputcsv($handle, [
            'timestamp',
            'elapsed',
            'label',
            'responseCode',
            'success',
            'URL',
            'Variables',
        ]);
    }

    /**
     * @param resource $handle
     */
    public function writeLine(
        $handle,
        float $duration,
        string $routeName,
        int $statusCode,
        bool $isSuccessful,
        string $relativeUrl,
        int $queryCount,
    ): void {
        fputcsv($handle, [
            time(),
            round($duration),
            $routeName,
            $statusCode,
            $isSuccessful ? 'true' : 'false',
            '/' . $relativeUrl,
            $queryCount,
        ]);
    }
}
