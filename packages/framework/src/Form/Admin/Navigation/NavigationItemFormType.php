<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Navigation;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItem;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class NavigationItemFormType extends AbstractType
{
    public function __construct(
        private readonly RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer,
        private readonly CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
        private readonly CategoryFacade $categoryFacade,
        private readonly Localization $localization,
    ) {
    }

    public function getName(): string
    {
        return 'navigation_item_form';
    }

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('domainId', DomainType::class, [
                'required' => true,
                'label' => 'Domain',
            ])
            ->add('name', TextType::class, [
                'label' => 'Name',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter navigation item name']),
                ],
            ])
            ->add('url', TextType::class, [
                'label' => 'Link URL',
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter link URL']),
                ],
            ]);
        $this->addColumnFields($builder);
        $builder->add('actionBar', ActionBarType::class, [
            'back_route' => 'admin_navigation_list',
            'entity' => $options['navigationItem'],
        ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('navigationItem')
            ->setAllowedTypes('navigationItem', [NavigationItem::class, 'null'])
            ->setDefaults([
                'data_class' => NavigationItemData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    private function createCategoryColumnBuilder(
        string $fieldName,
        string $label,
        int $index,
        array $categoryPaths,
        FormBuilderInterface $builder,
    ): FormBuilderInterface {
        return $builder
            ->create($fieldName, SortableValuesType::class, [
                'label' => $label,
                'property_path' => sprintf('categoriesByColumnNumber[%d]', $index),
                'labels_by_value' => $categoryPaths,
                'required' => false,
            ])
            ->addViewTransformer($this->removeDuplicatesTransformer)
            ->addModelTransformer($this->categoriesIdsToCategoriesTransformer);
    }

    private function addColumnFields(FormBuilderInterface $builder): void
    {
        $categoryPaths = $this->categoryFacade->getFullPathsIndexedByIds(
            $this->localization->getCurrentLocaleForTranslatableEntities(),
        );

        $builder
            ->add(
                $this->createCategoryColumnBuilder(
                    'categoriesInFirstColumn',
                    t('First column categories'),
                    1,
                    $categoryPaths,
                    $builder,
                ),
            )
            ->add(
                $this->createCategoryColumnBuilder(
                    'categoriesInSecondColumn',
                    t('Second column categories'),
                    2,
                    $categoryPaths,
                    $builder,
                ),
            )
            ->add(
                $this->createCategoryColumnBuilder(
                    'categoriesInThirdColumn',
                    t('Third column categories'),
                    3,
                    $categoryPaths,
                    $builder,
                ),
            )
            ->add(
                $this->createCategoryColumnBuilder(
                    'categoriesInFourthColumn',
                    t('Fourth column categories'),
                    4,
                    $categoryPaths,
                    $builder,
                ),
            );
    }
}
