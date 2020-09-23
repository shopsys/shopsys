<?php

declare(strict_types=1);

namespace App\Form;

use App\Component\Pricing\PriceToAndPriceData;
use Shopsys\FrameworkBundle\Form\Constraints\NotNegativeMoneyAmount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PriceToAndPriceType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['domainId'] = $options['domainId'];
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('priceTo', MoneyType::class, [
                'scale' => 6,
                'required' => true,
                'invalid_message' => 'Please enter price in correct format (positive number with decimal separator)',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter price']),
                    new NotNegativeMoneyAmount(['message' => 'Price must be greater or equal to zero']),
                ],
                'label' => t('Cena do'),
            ])
            ->add('price', MoneyType::class, [
                'scale' => 6,
                'required' => true,
                'invalid_message' => 'Please enter price in correct format (positive number with decimal separator)',
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter price']),
                    new NotNegativeMoneyAmount(['message' => 'Price must be greater or equal to zero']),
                ],
                'label' => t('Price'),
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => PriceToAndPriceData::class
            ])
            ->setRequired('domainId')
            ->setAllowedTypes('domainId', ['int']);
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return FormType::class;
    }
}
