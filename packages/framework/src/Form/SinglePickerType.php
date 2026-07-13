<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;

final class SinglePickerType extends AbstractSinglePickerType
{
    #[Override]
    public function getBlockPrefix(): string
    {
        return 'single_picker';
    }
}
