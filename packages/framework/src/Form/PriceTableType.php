<?php

namespace Shopsys\FrameworkBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PriceTableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['currencies'] as $key => $currency) {
            /* @var $currency \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency */
            $builder
                ->add($key, MoneyType::class, [
                    'currency' => false,
                    'scale' => 6,
                    'required' => true,
                    'invalid_message' => 'Please enter price in correct format (positive number with decimal separator)',
                    'constraints' => [
                        new Constraints\NotBlank(['message' => 'Please enter price']),
                        new Constraints\GreaterThanOrEqual([
                            'value' => 0,
                            'message' => 'Price must be greater or equal to {{ compared_value }}',
                        ]),
                    ],
                ]);
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);
        $view->vars['base_prices'] = $options['base_prices'];
        $view->vars['currencies'] = $options['currencies'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['base_prices', 'currencies'])
            ->addAllowedTypes('base_prices', 'array')
            ->addAllowedTypes('currencies', 'array')
            ->setDefaults([
                'base_prices' => [],
                'compound' => true,
                'render_form_row' => false,
            ]);
    }

    public function getParent(): ?string
    {
        return FormType::class;
    }
}
