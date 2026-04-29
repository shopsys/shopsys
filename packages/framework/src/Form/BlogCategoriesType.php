<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form;

use Override;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BlogCategoriesType extends AbstractType
{
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
    ) {
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'branch_load_route' => 'admin_blogcategory_loadbranchjson',
            'data_provider' => $this->blogCategoryFacade,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getParent(): string
    {
        return TreeSelectionType::class;
    }
}
