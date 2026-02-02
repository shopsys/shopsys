<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Autocomplete;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\GroupType;
use Shopsys\FrameworkBundle\Form\ProductsType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\BrandsIdsToBrandsTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteData;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AutocompleteFavoriteFormType extends AbstractType
{
    public function __construct(
        protected readonly BrandFacade $brandFacade,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly BrandsIdsToBrandsTransformer $brandsIdsToBrandsTransformer,
        protected readonly CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
        protected readonly RemoveDuplicatesFromArrayTransformer $removeDuplicatesFromArrayTransformer,
    ) {
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $domainId = $options['domain_id'];
        $locale = $options['locale'];

        $categoryPaths = $this->categoryFacade->getFullPathsIndexedByIdsForDomain($domainId, $locale);

        $brands = $this->brandFacade->getAll();
        $brandNames = [];

        foreach ($brands as $brand) {
            $brandNames[$brand->getId()] = $brand->getName();
        }

        $builder->add(
            $builder->create('products', ProductsType::class, [
                'required' => false,
                'label' => 'Favorite Products',
                'sortable' => true,
                'allow_variants' => false,
                'attr' => [
                    'placeholder' => 'Search and select products...',
                ],
            ])->addModelTransformer(
                $this->removeDuplicatesFromArrayTransformer,
            ),
        );

        $categoriesGroup = $builder->create('categoriesGroup', GroupType::class, [
            'label' => 'Favorite Categories',
        ]);
        $categoriesGroup->add(
            $builder->create('categories', SortableValuesType::class, [
                'required' => false,
                'labels_by_value' => $categoryPaths,
            ])->addModelTransformer(
                $this->categoriesIdsToCategoriesTransformer,
            )->addModelTransformer(
                $this->removeDuplicatesFromArrayTransformer,
            ),
        );

        $brandsGroup = $builder->create('brandsGroup', GroupType::class, [
            'label' => 'Favorite Brands',
        ]);
        $brandsGroup->add(
            $builder->create('brands', SortableValuesType::class, [
                'required' => false,
                'labels_by_value' => $brandNames,
            ])->addModelTransformer(
                $this->brandsIdsToBrandsTransformer,
            )->addModelTransformer(
                $this->removeDuplicatesFromArrayTransformer,
            ),
        );

        $builder
            ->add($categoriesGroup)
            ->add($brandsGroup)
            ->add('actionBar', ActionBarType::class, [
                'back_route' => null,
                'save_label' => t('Save changes'),
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['domain_id', 'locale'])
            ->setAllowedTypes('domain_id', 'int')
            ->setAllowedTypes('locale', 'string')
            ->setDefaults([
                'attr' => ['novalidate' => 'novalidate'],
                'data_class' => AutocompleteFavoriteData::class,
            ]);
    }
}
