<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\ProductReview;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductReviewImagesType extends AbstractType
{
    #[Override]
    public function getParent(): string
    {
        return CollectionType::class;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'renders_in_own_card' => true,
        ]);
    }
}
