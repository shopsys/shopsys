<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;

/**
 * Choice of `{placeholder}` strings, e.g. `{product_image}`
 */
class VariableField extends ChoiceField
{
    // The framework must not depend on the administration bundle, so the type is referenced by name only.
    #[Override]
    public function getFormType(): string
    {
        return 'Shopsys\AdministrationBundle\Form\Admin\Type\VariableType';
    }
}
