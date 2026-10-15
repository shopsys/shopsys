<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;

class ProductsField extends AbstractField
{
    #[Override]
    protected mixed $default = [];

    // The framework must not depend on the administration bundle, so the type is referenced by name only.
    #[Override]
    public function getFormType(): string
    {
        return 'Shopsys\AdministrationBundle\Form\Admin\Type\ProductIdsType';
    }

    #[Override]
    public function getFormOptions(): array
    {
        return parent::getFormOptions() + ['sortable' => true, 'renders_in_own_card' => false];
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
