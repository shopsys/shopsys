<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Product\Parameter\Value;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueConversionData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class ParameterValueConversionFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['locale'] = $view->vars['data']->locale;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $constraints = [
            new Constraints\Length(
                max: 255,
                maxMessage: 'Parameter value cannot be longer than {{ limit }} characters',
            ),
            new Constraints\NotBlank(message: 'Please enter parameter value'),
        ];

        if (isset($options['type'])) {
            $constraints[] = new Constraints\Type(
                type: $options['type'],
                message: 'Parameter value must be of type {{ type }}',
            );
        }

        $builder
            ->add('oldValueText', TextType::class, [
                'required' => true,
                'attr' => ['readonly' => 'readonly'],
            ])
            ->add('newValueText', TextType::class, [
                'required' => true,
                'constraints' => $constraints,
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
            'data_class' => ParameterValueConversionData::class,
        ])
        ->setRequired('type')
        ->setAllowedTypes('type', 'string');
    }
}
