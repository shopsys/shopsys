<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Price;

use Override;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductInputPriceData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ProductPricesWithVatSelectType extends AbstractType
{
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vat', ChoiceType::class, [
                'block_prefix' => 'prices_select_vat_input',
                'required' => true,
                'choices' => $this->vatFacade->getAllForDomainIncludingMarkedForDeletion($options['domain_id']),
                'choice_label' => 'name',
                'choice_value' => 'id',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter VAT rate']),
                ],
                'label' => 'VAT',
            ])
            ->add(
                'manualInputPricesByPricingGroupId',
                PricesByPricingGroupsType::class,
                [
                    'label' => false,
                    'required' => false,
                    'domain_id' => $options['domain_id'],
                    'product_prices' => $options['product_prices'],
                ],
            );
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'product_prices' => null,
                'data_class' => ProductInputPriceData::class,
            ])
            ->setRequired(['domain_id'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('product_prices', ['array', 'null']);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domain_id'] = $options['domain_id'];
    }
}
