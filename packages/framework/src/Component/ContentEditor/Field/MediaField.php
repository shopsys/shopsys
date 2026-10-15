<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;

class MediaField extends AbstractField
{
    // The framework must not depend on the administration bundle, so the type is referenced by name only.
    #[Override]
    public function getFormType(): string
    {
        return 'Shopsys\AdministrationBundle\Form\Admin\Type\MediaType';
    }

    #[Override]
    public function normalizeValue(mixed $value): ?int
    {
        return is_numeric($value) && (int)$value > 0 ? (int)$value : null;
    }
}
