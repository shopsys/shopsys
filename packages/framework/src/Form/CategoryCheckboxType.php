<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryCheckboxType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(private readonly CategoryFacade $categoryFacade)
    {
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param mixed[] $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $categoryId = $form->getName();

        if (is_numeric($categoryId)) {
            $category = $this->categoryFacade->getById((int)$categoryId);

            $view->vars['visible'] = $category->isVisible($options['domain_id']);
            $view->vars['has_children'] = $category->hasChildren();
            $view->vars['category_name'] = $category->getName();
            $view->vars['level'] = $category->getLevel();
        } else {
            $view->vars['visible'] = null;
            $view->vars['has_children'] = null;
            $view->vars['category_name'] = '__category_name__';
            $view->vars['level'] = 0;
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->setAllowedTypes('domain_id', 'int');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return CheckboxType::class;
    }
}
