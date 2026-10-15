<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TextareaField extends TextField
{
    #[Override]
    public function getFormType(): string
    {
        return TextareaType::class;
    }
}
