<?php

declare(strict_types=1);

namespace App\Form\Admin\Product\Type;

use App\Model\Product\Type\ProductType;
use App\Model\Product\Type\ProductTypeData;
use App\Model\Product\Type\ProductTypeFacade;
use Shopsys\FrameworkBundle\Form\Locale\LocalizedType;
use Symfony\Component\Form\AbstractType;
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
                ],
            ]);
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
                    new Constraints\Callback(function (ProductTypeData $productTypeData, ExecutionContextInterface $context) {
                        $existingProductType = $this->productTypeFacade->findByAkeneoCode($productTypeData->akeneoCode);
                        if ($existingProductType !== null
                            && $this->editedProductType !== null
                            && $existingProductType !== $this->editedProductType
                        ) {
                            $context->addViolation(t(
                                'Zadaný Akeneo kód "%akeneoCode%" již používá jiný typ produktu.',
                                ['%akeneoCode%' => $productTypeData->akeneoCode]
                            ));
                        }
                    }),
                ],
            ]);
    }
}
