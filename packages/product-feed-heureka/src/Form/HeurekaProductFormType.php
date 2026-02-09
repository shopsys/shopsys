<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Override;
use Shopsys\FormTypesBundle\MultidomainType;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Form\Constraints\MoneyRange;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class HeurekaProductFormType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('cpc', MultidomainType::class, [
            'label' => $this->translator->trans('Maximum price per click'),
            'entry_type' => MoneyType::class,
            'required' => false,
            'layout' => 'block',
            'display_mode' => 'columns',
            'entry_options' => [
                'currency' => 'CZK',
                'constraints' => [
                    new MoneyRange(
                        min: Money::zero(),
                        max: Money::create(500),
                    ),
                ],
            ],
        ]);
    }
}
