<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

class ProductListOrderingConfig
{
    public const string ORDER_BY_PRIORITY = 'priority';
    public const string ORDER_BY_PRICE_DESC = 'price_desc';
    public const string ORDER_BY_PRICE_ASC = 'price_asc';
    public const string ORDER_BY_NAME_DESC = 'name_desc';
    public const string ORDER_BY_RELEVANCE = 'relevance';
    public const string ORDER_BY_NAME_ASC = 'name_asc';

    /**
     * @param string[] $supportedOrderingModesNamesById
     * @param string $defaultOrderingModeId
     */
    public function __construct(
        protected readonly array $supportedOrderingModesNamesById,
        protected readonly string $defaultOrderingModeId,
    ) {
    }

    /**
     * @return string[]
     */
    public function getSupportedOrderingModesNamesIndexedById(): array
    {
        return $this->supportedOrderingModesNamesById;
    }

    /**
     * @return string
     */
    public function getDefaultOrderingModeId(): string
    {
        return $this->defaultOrderingModeId;
    }

    /**
     * @return string[]
     */
    public function getSupportedOrderingModeIds(): array
    {
        return array_keys($this->supportedOrderingModesNamesById);
    }
}
