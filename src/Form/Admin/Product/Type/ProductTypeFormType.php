<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Type;

use App\Model\Product\Type\ProductType;
use App\Model\Product\Type\ProductTypeData;
use App\Model\Product\Type\ProductTypeFacade;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FormTypesBundle\YesNoType;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProductTypeFormType extends AbstractType
{
    /**
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    private $productTypeFacade;

    /**
     * @var \App\Model\Product\Type\ProductType|null
     */
    private $editedProductType;

    /**
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     */
    public function __construct(ProductTypeFacade $productTypeFacade)
    {
        $this->productTypeFacade = $productTypeFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->editedProductType = $options['edited_product_type'];

        $builder
            ->add('name', LocalizedType::class, [
                'required' => true,
                'entry_options' => [
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Prosím vyplňte typ produktu ve všech jazycích.']),
                        new Constraints\Length(['max' => 100, 'maxMessage' => 'Název nesmí být delší než {{ limit }} znaků.']),
                    ],
                ],
            ])
            ->add('akeneoCode', TextType::class, [
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Prosím vyplňte Akaneo kód']),
                    new Constraints\Length([
                        'max' => 20,
                        'maxMessage' => 'Akaneo kód nesmí být delší než {{ limit }} znaků.',
                    ]),
                    new Constraints\Callback(function ($akeneoCode, ExecutionContextInterface $context) {
                        $existingProductType = $this->productTypeFacade->findByAkeneoCode($akeneoCode);
                        if ($existingProductType !== null
                            && $this->editedProductType !== null
                            && $existingProductType !== $this->editedProductType
                        ) {
                            $context->addViolation(t(
                                'Zadaný Akeneo kód "%akeneoCode%" již používá jiný typ produktu.',
                                ['%akeneoCode%' => $akeneoCode]
                            ));
                        }
                    }),
                ],
            ])
            ->add('freeTransport', MultidomainType::class, [
                'entry_type' => YesNoType::class,
                'required' => false,
                'label' => t('Povolit dopravu zdarma'),
            ])
            ->add('freeTransportMinimalPrice', MultidomainType::class, [
                'label' => t('Minimální částka pro dopravu zdarma s DPH'),
                'entry_type' => MoneyType::class,
                'error_bubbling' => false,
                'entry_options' => [
                    'scale' => 6,
                ],
                'required' => false,
            ]);

        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('edited_product_type')
            ->addAllowedTypes('edited_product_type', [ProductType::class, 'null'])
            ->setDefaults([
                'data_class' => ProductTypeData::class,
                'attr' => ['novalidate' => 'novalidate'],
                'constraints' => [
                    new Constraints\Callback([$this, 'validateFreeTransportMinimalPriceByDomain']),
                ],
            ]);
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     * @param \Symfony\Component\Validator\Context\ExecutionContextInterface $context
     */
    public function validateFreeTransportMinimalPriceByDomain(ProductTypeData $productTypeData, ExecutionContextInterface $context): void
    {
        foreach ($productTypeData->freeTransport as $domainId => $freeTransport) {
            if ($freeTransport === true) {
                if ($productTypeData->freeTransportMinimalPrice[$domainId] === null) {
                    $context->buildViolation('Pokud je povolená doprava zdarma, vyplňte minimální částku pro danou doménu.')
                        ->atPath('freeTransportMinimalPrice')
                        ->addViolation();
                }

                if ($productTypeData->freeTransportMinimalPrice[$domainId]->getAmount() < 0) {
                    $context->buildViolation('Minimální částka musí větší, nebo rovna 0.')
                        ->atPath('freeTransportMinimalPrice')
                        ->addViolation();
                }
            }
        }
    }
}
