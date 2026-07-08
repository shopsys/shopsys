<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Currency;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Symfony\Component\HttpFoundation\Request;

/**
 * An unknown or not enabled currency code silently falls back to the domain default currency
 * (the fallback is handled by the CurrentCurrencyProvider itself)
 */
class CurrencyHeaderInitializer
{
    public const string HEADER_CURRENCY_CODE = 'X-Currency-Code';

    public function __construct(protected readonly CurrentCurrencyProvider $currentCurrencyProvider)
    {
    }

    public function initializeFromRequest(Request $request): void
    {
        $this->currentCurrencyProvider->setCurrentCurrencyCode(
            $request->headers->get(static::HEADER_CURRENCY_CODE),
        );
    }
}
