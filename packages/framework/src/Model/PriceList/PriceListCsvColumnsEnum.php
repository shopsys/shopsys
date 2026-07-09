<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Override;
use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;
use Shopsys\FrameworkBundle\Model\Pricing\ProductPricesMulticurrencyModeProvider;

class PriceListCsvColumnsEnum extends AbstractEnum
{
    public const string PRODUCT_CATNUM = 'product_catnum';
    public const string PRICE = 'price';
    public const string CURRENCY_CODE = 'currency_code';

    public function __construct(
        protected readonly ProductPricesMulticurrencyModeProvider $productPricesMulticurrencyModeProvider,
    ) {
    }

    /**
     * @return string[]
     */
    #[Override]
    protected function getUnusedConstants(): array
    {
        if ($this->productPricesMulticurrencyModeProvider->isCalculatedMode()) {
            return [self::CURRENCY_CODE];
        }

        return [];
    }
}
