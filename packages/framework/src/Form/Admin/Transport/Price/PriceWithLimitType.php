<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Transport\Price;

use Override;
use Shopsys\FrameworkBundle\Model\Transport\PriceWithLimitData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PriceWithLimitType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', MoneyType::class, [
                'scale' => 6,
                'constraints' => [
                    new NotBlank(['message' => 'Please enter price']),
                ],
            ])
            ->add('maxWeight', IntegerType::class)
            ->add('transportPriceId', HiddenType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => PriceWithLimitData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ])
            ->setRequired(['domain_id', 'current_transport_prices_indexed_by_id'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('current_transport_prices_indexed_by_id', 'array');
    }

    /**
     * @return string
     */
    #[Override]
    public function getParent(): string
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $transportPriceId = (int)$form->get('transportPriceId')->getData();
        $view->vars['domain_id'] = $options['domain_id'];
        $view->vars['transport_calculated_price'] = $options['current_transport_prices_indexed_by_id'][$transportPriceId] ?? null;
    }
}
