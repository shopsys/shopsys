<?php

declare(strict_types=1);

namespace App\Component\Collector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Throwable;

class TwigCacheDataCollector extends DataCollector
{
    /**
     * @var bool
     */
    protected bool $twigCacheEnabled;

    /**
     * @param bool $twigCacheEnabled
     */
    public function __construct(bool $twigCacheEnabled)
    {
        $this->twigCacheEnabled = $twigCacheEnabled;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param \Throwable|null $exception
     */
    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        $this->data = [
            'isCacheEnabled' => $this->twigCacheEnabled,
        ];
    }

    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'app.twig_cache_collector';
    }

    /**
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        return $this->data['isCacheEnabled'];
    }

    /**
     * @return string
     */
    public function getCacheStatus(): string
    {
        return $this->isCacheEnabled() ? 'on' : 'off';
    }
}
