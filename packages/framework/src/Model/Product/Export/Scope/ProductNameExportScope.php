<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

class ProductNameExportScope extends AbstractProductExportScope
{
    public function map(object $object, string $locale, int $domainId): array
    {
        return [
            'name' => $object->getName($locale),
        ];
    }

    public function getDependencies(): array
    {
        return [
            ProductUrlExportScope::class,
        ];
    }
}