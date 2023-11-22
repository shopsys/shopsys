<?php

declare(strict_types=1);

namespace App\Form\Admin\Navigation;

use App\Component\Locale\LocaleHelper;
use App\Model\Category\CategoryFacade;
use App\Model\Navigation\NavigationItem;
use App\Model\Navigation\NavigationItemData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class NavigationItemFormType extends AbstractType
{
    /**
     * @var \App\Model\Category\Category[]
     */
    private array $categoryPaths;

    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer
     * @param \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer
     * @param \App\Model\Category\CategoryFacade $categoryFacade
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
    public function getName(): string
    {
        return 'navigation_item_form';
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
                    new Constraints\NotBlank(['message' => 'Please enter article name']),
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
