<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Enum\InvalidEnumCaseException;
use Shopsys\FrameworkBundle\Model\Pricing\ProductPricesMulticurrencyModeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\ProductPricesMulticurrencyModeProvider;

class ProductPricesMulticurrencyModeProviderTest extends TestCase
{
    public function testCalculatedMode(): void
    {
        $provider = new ProductPricesMulticurrencyModeProvider(
            ProductPricesMulticurrencyModeEnum::MODE_CALCULATED,
            new ProductPricesMulticurrencyModeEnum(),
        );

        $this->assertTrue($provider->isCalculatedMode());
        $this->assertFalse($provider->isManualMode());
    }

    public function testManualMode(): void
    {
        $provider = new ProductPricesMulticurrencyModeProvider(
            ProductPricesMulticurrencyModeEnum::MODE_MANUAL,
            new ProductPricesMulticurrencyModeEnum(),
        );

        $this->assertTrue($provider->isManualMode());
        $this->assertFalse($provider->isCalculatedMode());
    }

    public function testInvalidModeThrowsException(): void
    {
        $provider = new ProductPricesMulticurrencyModeProvider(
            'unknown-mode',
            new ProductPricesMulticurrencyModeEnum(),
        );

        $this->expectException(InvalidEnumCaseException::class);
        $provider->getMode();
    }
}
