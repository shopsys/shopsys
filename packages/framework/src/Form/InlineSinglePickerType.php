<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;

final class InlineSinglePickerType extends AbstractSinglePickerType
{
    #[Override]
    public function getBlockPrefix(): string
    {
        return 'inline_single_picker';
    }
}
