<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaleCategoryFormType extends AbstractType
{
    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade
    ) {
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $categoryPaths = $this->categoryFacade->getAllVisibleCategoriesByDomainId(
            $options['domain_id']
        );

        $builder->add(
            $builder->create('category', ChoiceType::class, [
                'required' => false,
                'choices' => $categoryPaths,
                'choice_label' => function (Category $category) {
                    $padding = str_repeat("-", ($category->getLevel() - 1) * 2);
                    return $padding . $category->getName();
                },
                'choice_value' => 'id',
                'label' => t('Nastavení kategorie výprodej'),
                'placeholder' => t('-- Vyberte kategorii výprodej --'),
            ])
        )
        ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['domain_id'])
            ->setAllowedTypes('domain_id', 'int')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
