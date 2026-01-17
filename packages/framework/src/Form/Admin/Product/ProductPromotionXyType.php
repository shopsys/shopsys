<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product;

use Override;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ProductPromotionXyType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('buyQuantity', IntegerType::class, [
                'label' => t('Buy quantity'),
                'constraints' => [
                    new Constraints\When(
                        expression: 'this.getParent().get("freeQuantity").getData() !== null',
                        constraints: [
                            new Constraints\NotBlank(),
                        ],
                    ),
                    new Constraints\GreaterThan(
                        ['value' => 0, 'message' => 'Quantity must be greater than {{ compared_value }}'],
                    ),
                ],
            ])
            ->add('freeQuantity', IntegerType::class, [
                'label' => t('Free quantity'),
                'constraints' => [
                    new Constraints\When(
                        expression: 'this.getParent().get("buyQuantity").getData() !== null',
                        constraints: [
                            new Constraints\NotBlank(),
                        ],
                    ),
                    new Constraints\GreaterThan(
                        ['value' => 0, 'message' => 'Quantity must be greater than {{ compared_value }}'],
                    ),
                ],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductPromotionXyData::class,
        ]);
    }
}
