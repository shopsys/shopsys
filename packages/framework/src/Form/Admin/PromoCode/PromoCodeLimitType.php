<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Override;
use Shopsys\FrameworkBundle\Form\Admin\PromoCode\Transformer\PromoCodeLimitTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class PromoCodeLimitType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\PromoCode\Transformer\PromoCodeLimitTransformer $promoCodeLimitTransformer
     */
    public function __construct(private PromoCodeLimitTransformer $promoCodeLimitTransformer)
    {
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('discount')
            ->addAllowedTypes('discount', 'array');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
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

        $builder->addModelTransformer($this->promoCodeLimitTransformer);
    }
}
