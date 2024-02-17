<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

// TODO na projektu nikoho nenutím ten scope vytvářet, když jej nepotřebuje, tam mu stačí přidat záznam do ProductExportScopeEnum + ProductExportRepository
class ProductSimplePropertyExportScope extends AbstractProductExportScope
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $object
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    public function map(object $object, string $locale, int $domainId): array
    {
        return [
            'partno' => $object->getPartno(),
            'catnum' => $object->getCatnum(),
            'ean' => $object->getEan(),
            'id' => $object->getId(),
            'uuid' => $object->getUuid(),
            'description' => $object->getDescription($domainId),
            'short_description' => $object->getShortDescription($domainId),
            'ordering_priority' => $object->getOrderingPriority($domainId),
            'unit' => $object->getUnit()->getName($locale),
            'seo_h1' => $object->getSeoH1($domainId),
            'seo_meta_description' => $object->getSeoMetaDescription($domainId),
            'seo_title' => $object->getSeoTitle($domainId),
        ];
    }
}
