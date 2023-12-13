<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogCategoryCheckboxType extends AbstractType
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     */
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $blogCategoryId = $form->getName();

        if (is_numeric($blogCategoryId)) {
            $blogCategory = $this->blogCategoryFacade->getById((int)$blogCategoryId);

            $view->vars['visible'] = $blogCategory->isVisible($options['domain_id']);
            $view->vars['has_children'] = $blogCategory->hasChildren();
            $view->vars['category_name'] = $blogCategory->getName();
            $view->vars['level'] = $blogCategory->getLevel();
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
     * @return string
     */
    public function getParent(): string
    {
        return CheckboxType::class;
    }
}
