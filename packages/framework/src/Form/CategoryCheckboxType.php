<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CategoryCheckboxType extends AbstractType
{
    public function __construct(
        private readonly CategoryFacade $categoryFacade,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    #[Override]
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $categoryId = $form->getName();

        if (is_numeric($categoryId)) {
            $category = $this->categoryFacade->getById((int)$categoryId);

            $view->vars['visible'] = $category->isVisible($options['domain_id']);
            $view->vars['has_children'] = $category->hasChildren();
            $view->vars['label'] = $category->getName();
            $view->vars['level'] = $category->getLevel();
        } else {
            $view->vars['visible'] = null;
            $view->vars['has_children'] = null;
            $view->vars['label'] = '__category_name__';
            $view->vars['level'] = 0;
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('domain_id')
            ->setAllowedTypes('domain_id', 'int');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return CheckboxType::class;
    }
}
