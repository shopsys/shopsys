<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Override;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\Transformer\PromoCodeLimitTransformer;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class PromoCodeLimitType extends AbstractType
{
    public function __construct(
        private readonly PromoCodeLimitFactory $promoCodeLimitFactory,
        private readonly CurrencyFacade $currencyFacade,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('discount')
            ->addAllowedTypes('discount', 'array')
            ->setRequired('currency_code')
            ->addAllowedTypes('currency_code', 'string');
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('fromPrice', NumberType::class, [
            'constraints' => [
                new Constraints\NotBlank(
                    message: 'Please enter limit from',
                    groups: [PromoCodeFormType::VALIDATION_GROUP_TYPE_PERCENT, PromoCodeFormType::VALIDATION_GROUP_TYPE_NOMINAL],
                ),
            ],
            'scale' => 6,
        ]);

        $currencyCode = $options['currency_code'];
        $options = $options['discount'];

        foreach ($options['constraints'] as $constraint) {
            $constraint->groups = [PromoCodeFormType::VALIDATION_GROUP_TYPE_PERCENT];
        }

        $options['constraints'][] = new Constraints\NotBlank(
            groups: [PromoCodeFormType::VALIDATION_GROUP_TYPE_NOMINAL],
        );
        $options['constraints'][] = new Constraints\GreaterThanOrEqual(
            value: 1,
            groups: [PromoCodeFormType::VALIDATION_GROUP_TYPE_NOMINAL],
        );
        $options['constraints'][] = new Constraints\Regex(
            pattern: '/^\d+$/',
            message: 'Please enter a whole number.',
            groups: [PromoCodeFormType::VALIDATION_GROUP_TYPE_PERCENT],
        );
        $options['scale'] = 6;
        $builder->add(
            'discount',
            NumberType::class,
            $options,
        );

        $builder->addModelTransformer(new PromoCodeLimitTransformer(
            $this->promoCodeLimitFactory,
            $this->currencyFacade,
            $currencyCode,
        ));
    }
}
