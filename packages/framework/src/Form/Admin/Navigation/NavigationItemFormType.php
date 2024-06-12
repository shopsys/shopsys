<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Navigation;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Locale\LocaleHelper;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItem;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class NavigationItemFormType extends AbstractType
{
    /**
     * @var string[]
     */
    private array $categoryPaths;

    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer
     * @param \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        private RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer,
        private CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
        CategoryFacade $categoryFacade,
    ) {
        $this->categoryPaths = $categoryFacade->getFullPathsIndexedByIdsForDomain(Domain::FIRST_DOMAIN_ID, LocaleHelper::LOCALE_CS);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'navigation_item_form';
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('domainId', DomainType::class, [
                'required' => true,
                'label' => t('Domain'),
            ])
            ->add('name', TextType::class, [
                'label' => t('Name'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter navigation item name']),
                ],
            ])
            ->add('url', TextType::class, [
                'label' => t('Link URL'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter link URL']),
                ],
            ]);
        $this->addColumnFields($builder);
        $builder->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('navigationItem')
            ->setAllowedTypes('navigationItem', [NavigationItem::class, 'null'])
            ->setDefaults([
                'data_class' => NavigationItemData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }

    /**
     * @param string $fieldName
     * @param string $label
     * @param int $index
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @return \Symfony\Component\Form\FormBuilderInterface
     */
    private function createCategoryColumnBuilder(
        string $fieldName,
        string $label,
        int $index,
        FormBuilderInterface $builder,
    ): FormBuilderInterface {
        return $builder
            ->create($fieldName, SortableValuesType::class, [
                'label' => $label,
                'property_path' => sprintf('categoriesByColumnNumber[%d]', $index),
                'labels_by_value' => $this->categoryPaths,
                'required' => false,
            ])
            ->addViewTransformer($this->removeDuplicatesTransformer)
            ->addModelTransformer($this->categoriesIdsToCategoriesTransformer);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    private function addColumnFields(FormBuilderInterface $builder): void
    {
        $builder->add(
            $this->createCategoryColumnBuilder(
                'categoriesInFirstColumn',
                'Kategorie prvního sloupce',
                1,
                $builder,
            ),
        )
        ->add(
            $this->createCategoryColumnBuilder(
                'categoriesInSecondColumn',
                'Kategorie druhého sloupce',
                2,
                $builder,
            ),
        )
        ->add(
            $this->createCategoryColumnBuilder(
                'categoriesInThirdColumn',
                'Kategorie třetího sloupce',
                3,
                $builder,
            ),
        )
        ->add(
            $this->createCategoryColumnBuilder(
                'categoriesInFourthColumn',
                'Kategorie čtvrtého sloupce',
                4,
                $builder,
            ),
        );
    }
}
