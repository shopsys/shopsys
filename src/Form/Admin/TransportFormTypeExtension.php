<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Product\Type\ProductTypeFacade;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class TransportFormTypeExtension extends AbstractTypeExtension
{
    /**
     * @var \App\Model\Product\Type\ProductTypeFacade
     */
    private $productTypeFacade;

    /**
     * @param \App\Model\Product\Type\ProductTypeFacade $productTypeFacade
     */
    public function __construct(ProductTypeFacade $productTypeFacade)
    {
        $this->productTypeFacade = $productTypeFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->get('basicInformation')
            ->add('productTypes', ChoiceType::class, [
            'required' => false,
            'choices' => $this->productTypeFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
            'multiple' => true,
            'expanded' => true,
            'label' => t('Určeno pro typy produktů'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield TransportFormType::class;
    }
}
