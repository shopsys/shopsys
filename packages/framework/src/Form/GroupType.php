<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class GroupType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setAllowedTypes('label', 'string')
            ->setDefaults([
                'inherit_data' => true,
                'renders_in_own_card' => true,
            ]);
    }
}
