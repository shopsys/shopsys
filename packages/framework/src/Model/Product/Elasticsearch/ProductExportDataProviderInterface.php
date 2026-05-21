<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Shopsys\FrameworkBundle\Model\Product\Product;

interface ProductExportDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getExportFields(): array;

    /**
     * @return array<string, string[]>
     */
    public function getExportFieldsByScope(): array;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function loadProductExportData(array $products, int $domainId, string $locale): void;

    public function getExportedFieldValue(Product $product, int $domainId, string $locale, string $field): mixed;

    public function getDefaultValue(string $field): mixed;
}
