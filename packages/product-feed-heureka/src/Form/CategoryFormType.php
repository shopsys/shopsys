<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Form;

use Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CategoryFormType extends AbstractType
{
    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade
     */
    private $heurekaCategoryFacade;

    public function __construct(
        TranslatorInterface $translator,
        HeurekaCategoryFacade $heurekaCategoryFacade
    ) {
        $this->translator = $translator;
        $this->heurekaCategoryFacade = $heurekaCategoryFacade;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $heurekaCategories = $this->heurekaCategoryFacade->getAllIndexedById();

        $builder->add('heureka_category', ChoiceType::class, [
            'label' => $this->translator->trans('Heureka category'),
            'choices' => $heurekaCategories,
            'required' => false,
            'attr' => ['class' => 'js-autocomplete-selectbox'],
            'choice_label' => 'getName',
        ]);
    }
}
