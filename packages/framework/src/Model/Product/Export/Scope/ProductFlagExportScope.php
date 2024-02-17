<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductFlagExportScope extends AbstractProductExportScope
{
    /**
     * @param Product $object
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    public function map(object $object, string $locale, int $domainId): array
    {
        return [
            'flags' => $this->extractFlags($domainId, $object),
        ];
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function extractFlags(int $domainId, Product $product): array
    {
        $flagIds = [];

        foreach ($product->getFlags($domainId) as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
    }
}
