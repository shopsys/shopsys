<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\CsFixer;

use Override;

class FinalFormTypeFixer extends AbstractFinalClassByParentFixer
{
    #[Override]
    protected function getDescription(): string
    {
        return 'Form types extending AbstractType or AbstractTypeExtension must be final.';
    }

    #[Override]
    protected function getMatchingParentClasses(): array
    {
        return [
            'Symfony\Component\Form\AbstractType',
            'Symfony\Component\Form\AbstractTypeExtension',
        ];
    }

    #[Override]
    public function getName(): string
    {
        return 'Shopsys/final_form_type';
    }
}
