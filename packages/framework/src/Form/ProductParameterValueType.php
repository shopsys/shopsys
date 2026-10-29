<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\ProductParameterValueFormType;
use Shopsys\FrameworkBundle\Form\Constraints\UniqueProductParameters;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProductParameterValueType extends AbstractType
{
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entry_type' => ProductParameterValueFormType::class,
            'add_label' => t('Add another parameter'),
            'empty_message' => t('Add some parameters'),
            'constraints' => [
                new UniqueProductParameters(
                    message: 'Parameter {{ parameterName }} is used more than once',
                ),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return InteractiveCollectionType::class;
    }
}
