<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;
use Shopsys\FrameworkBundle\Form\ProductsType;

class ProductsField extends AbstractField
{
    #[Override]
    protected mixed $default = [];

    #[Override]
    public function getFormType(): string
    {
        return ProductsType::class;
    }

    /**
     * @return int[]
     */
    #[Override]
    public function normalizeValue(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $ids = [];

        foreach ($value as $id) {
            if (is_numeric($id) && (int)$id > 0 && !in_array((int)$id, $ids, true)) {
                $ids[] = (int)$id;
            }
        }

        return $ids;
    }
}
