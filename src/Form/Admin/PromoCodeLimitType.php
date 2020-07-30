<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Admin\Transformer\PromoCodeLimitTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PromoCodeLimitType extends AbstractType
{
    /**
     * @var \App\Form\Admin\Transformer\PromoCodeLimitTransformer
     */
    private $promoCodeLimitTransformer;

    /**
     * @param \App\Form\Admin\Transformer\PromoCodeLimitTransformer $promoCodeLimitTransformer
     */
    public function __construct(PromoCodeLimitTransformer $promoCodeLimitTransformer)
    {
        $this->promoCodeLimitTransformer = $promoCodeLimitTransformer;
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('percent')
            ->addAllowedTypes('percent', 'array');
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('fromPriceWithVat', IntegerType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Prosím vložte limit od',
                ]),
            ],
        ]);
        $builder->add(
            'percent',
            IntegerType::class,
            $options['percent']
        );
        $builder->addModelTransformer($this->promoCodeLimitTransformer);
    }
}
