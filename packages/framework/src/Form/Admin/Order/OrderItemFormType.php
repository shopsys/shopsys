<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Order;

use Override;
use Shopsys\FrameworkBundle\Form\Transformers\InverseTransformer;
use Shopsys\FrameworkBundle\Form\ValidationGroup;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class OrderItemFormType extends AbstractType
{
    public const string VALIDATION_GROUP_NOT_USING_PRICE_CALCULATION = 'notUsingPriceCalculation';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter name'),
                ],
            ])
            ->add('catnum', TextType::class, [
                'constraints' => [
                    new Constraints\Length(max: 255),
                ],
            ])
            ->add('unitPriceWithVat', MoneyType::class, [
                'scale' => 6,
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter unit price with VAT'),
                ],
            ])
            ->add('unitPriceWithoutVat', MoneyType::class, [
                'scale' => 6,
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter unit price without VAT',
                        groups: [self::VALIDATION_GROUP_NOT_USING_PRICE_CALCULATION],
                    ),
                ],
            ])
            ->add('totalPriceWithVat', MoneyType::class, [
                'scale' => 6,
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter total price with VAT',
                        groups: [self::VALIDATION_GROUP_NOT_USING_PRICE_CALCULATION],
                    ),
                ],
            ])
            ->add('totalPriceWithoutVat', MoneyType::class, [
                'scale' => 6,
                'constraints' => [
                    new Constraints\NotBlank(
                        message: 'Please enter total price without VAT',
                        groups: [self::VALIDATION_GROUP_NOT_USING_PRICE_CALCULATION],
                    ),
                ],
            ])
            ->add(
                $builder->create('setPricesManually', CheckboxType::class, [
                    'property_path' => 'usePriceCalculation',
                    'required' => false,
                    'label' => false,
                ])->addModelTransformer(new InverseTransformer()),
            )
            ->add('vatPercent', NumberType::class, [
                'input' => 'string',
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter VAT rate'),
                ],
            ])
            ->add('quantity', IntegerType::class, [
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please enter quantity'),
                    new Constraints\GreaterThan(
                        value: 0,
                        message: 'Quantity must be greater than {{ compared_value }}',
                    ),
                ],
            ])
            ->add('unitName', TextType::class, [
                'constraints' => [
                    new Constraints\Length(max: 10),
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderItemData::class,
            'attr' => ['novalidate' => 'novalidate'],
            'validation_groups' => function (FormInterface $form) {
                return self::resolveValidationGroups($form);
            },
            'empty_data' => fn () => $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PRODUCT),
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @return string[]
     */
    public static function resolveValidationGroups(FormInterface $form): array
    {
        $validationGroups = [ValidationGroup::VALIDATION_GROUP_DEFAULT];

        /** @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData */
        $orderItemData = $form->getData();

        if (!$orderItemData->usePriceCalculation) {
            $validationGroups[] = self::VALIDATION_GROUP_NOT_USING_PRICE_CALCULATION;
        }

        return $validationGroups;
    }
}
