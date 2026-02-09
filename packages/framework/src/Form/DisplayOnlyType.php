<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DisplayOnlyType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'mapped' => false,
                'required' => false,
                'disabled' => true,
                'compound' => false,
                'attr' => [
                    'readonly' => 'readonly',
                ],
            ]);
    }
}
