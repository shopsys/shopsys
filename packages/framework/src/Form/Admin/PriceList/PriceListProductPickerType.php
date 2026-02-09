<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PriceList;

use Override;
use Shopsys\FrameworkBundle\Form\Constraints\PositiveMoneyAmount;
use Shopsys\FrameworkBundle\Form\Transformers\NumericToMoneyTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\ProductIdToProductTransformer;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListProductPriceData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class PriceListProductPickerType extends AbstractType
{
    public function __construct(
        private readonly ProductIdToProductTransformer $productsIdToProductsTransformer,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('product', HiddenType::class);
        $builder->get('product')->addModelTransformer($this->productsIdToProductsTransformer);

        $builder->add('priceAmount', MoneyType::class, [
            'scale' => 6,
            'required' => true,
            'invalid_message' => 'Please enter price in correct format (positive number with decimal separator)',
            'constraints' => [
                new Constraints\NotBlank(message: 'Please enter price'),
                new PositiveMoneyAmount(message: 'Price must be greater to zero'),
            ],
        ]);

        $builder->add('basicPrice', HiddenType::class);
        $builder->get('basicPrice')->addModelTransformer(new NumericToMoneyTransformer(6));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriceListProductPriceData::class,
        ]);
    }
}
