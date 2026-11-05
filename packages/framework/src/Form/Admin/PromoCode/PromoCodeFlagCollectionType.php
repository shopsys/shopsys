<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Override;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\Constraint\UniqueFlags;
use Shopsys\FrameworkBundle\Form\InteractiveCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PromoCodeFlagCollectionType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => PromoCodeFlagType::class,
            'add_label' => t('Add another flag'),
            'empty_message' => t('Add some flag'),
            'constraints' => [
                new UniqueFlags(),
            ],
        ]);
    }

    #[Override]
    public function getParent(): string
    {
        return InteractiveCollectionType::class;
    }
}
