<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\PromoCode;

use Shopsys\FrameworkBundle\Form\Admin\PromoCode\Transformer\PromoCodeLimitTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PromoCodeLimitType extends AbstractType
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('discount')
            ->addAllowedTypes('discount', 'array');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fromPriceWithVat', IntegerType::class, [
            'constraints' => [
                new Constraints\NotBlank([
                    'message' => 'Please enter limit from',
                ]),
            ],
        ]);

        $options = $options['discount'];

        foreach ($options['constraints'] as $constraint) {
            $constraint->groups = [PromoCodeFormType::VALIDATION_GROUP_TYPE_PERCENT];
        }

        $options['constraints'][] = new Constraints\NotBlank([
            'groups' => [PromoCodeFormType::VALIDATION_GROUP_TYPE_NOMINAL],
        ]);
        $options['constraints'][] = new Constraints\GreaterThanOrEqual([
            'groups' => [PromoCodeFormType::VALIDATION_GROUP_TYPE_NOMINAL],
            'value' => 1,
        ]);
        $options['constraints'][] = new Constraints\Regex([
            'groups' => PromoCodeFormType::VALIDATION_GROUP_TYPE_NOMINAL,
            'pattern' => '/^\d+$/',
        ]);
        $options['scale'] = 3;
        $builder->add(
            'discount',
            NumberType::class,
            $options,
        );

        $builder->addModelTransformer($this->promoCodeLimitTransformer);
    }
}
