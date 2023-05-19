<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CategoryFormType extends AbstractType
{
    /**
     * @param \Symfony\Contracts\Translation\TranslatorInterface $translator
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade $heurekaCategoryFacade
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly HeurekaCategoryFacade $heurekaCategoryFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param  array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $heurekaCategories = $this->heurekaCategoryFacade->getAllIndexedById();

        $builder->add('heureka_category', ChoiceType::class, [
            'label' => $this->translator->trans('Heureka category'),
            'choices' => $heurekaCategories,
            'required' => false,
            'attr' => [
                'class' => 'js-autocomplete-selectbox',
            ],
            'choice_label' => 'getName',
        ]);
    }
}
