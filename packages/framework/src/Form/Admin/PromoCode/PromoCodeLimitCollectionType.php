<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Override;
use Shopsys\FrameworkBundle\Form\InteractiveCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PromoCodeLimitCollectionType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => PromoCodeLimitType::class,
            'add_label' => t('Add another limit'),
            'empty_message' => t('Add limit'),
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return InteractiveCollectionType::class;
    }
}
