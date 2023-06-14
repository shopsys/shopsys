<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Admin\Transformer\PromoCodeLimitTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PromoCodeLimitType extends AbstractType
{
    /**
     * @param \App\Form\Admin\Transformer\PromoCodeLimitTransformer $promoCodeLimitTransformer
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
                    'message' => 'Prosím vložte limit od',
                ]),
            ],
        ]);

        $options = $options['discount'];

        foreach ($options['constraints'] as $constraint) {
            $constraint->groups = [PromoCodeFormTypeExtension::VALIDATION_GROUP_TYPE_PERCENT];
        }

        $options['constraints'][] = new Constraints\NotBlank([
            'groups' => [PromoCodeFormTypeExtension::VALIDATION_GROUP_TYPE_NOMINAL],
        ]);
        $options['constraints'][] = new Constraints\GreaterThanOrEqual([
            'groups' => [PromoCodeFormTypeExtension::VALIDATION_GROUP_TYPE_NOMINAL],
            'value' => 1,
        ]);
        $options['constraints'][] = new Constraints\Regex([
            'groups' => PromoCodeFormTypeExtension::VALIDATION_GROUP_TYPE_NOMINAL,
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
