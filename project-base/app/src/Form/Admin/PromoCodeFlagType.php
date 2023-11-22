<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Form\Admin\Transformer\PromoCodeFlagTransformer;
use App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag;
use App\Model\Product\Flag\FlagFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class PromoCodeFlagType extends AbstractType
{
    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Form\Admin\Transformer\PromoCodeFlagTransformer $promoCodeFlagTransformer
     */
    public function __construct(
        private FlagFacade $flagFacade,
        private PromoCodeFlagTransformer $promoCodeFlagTransformer,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('flag', ChoiceType::class, [
            'required' => true,
            'choices' => $this->flagFacade->getAll(),
            'choice_label' => 'name',
            'choice_value' => 'id',
            'constraints' => [
                new NotBlank([
                    'message' => 'Please choose flag',
                ]),
            ],
        ]);

        $builder->add('type', ChoiceType::class, [
            'required' => true,
            'choices' => [
                t('Products with this flag') => PromoCodeFlag::TYPE_INCLUSIVE,
                t('Products without this flag') => PromoCodeFlag::TYPE_EXCLUSIVE,
            ],
            'expanded' => true,
            'multiple' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'Please choose type of limitation',
                ]),
            ],
        ]);

        $builder->addModelTransformer($this->promoCodeFlagTransformer);
    }
}
