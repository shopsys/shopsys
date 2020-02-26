<?php

declare(strict_types=1);

namespace App\Form\Admin\HorizontalMenu;

use App\Component\Locale\LocaleHelper;
use App\Model\Category\CategoryFacade;
use App\Model\HorizontalMenu\HorizontalMenuItem;
use App\Model\HorizontalMenu\HorizontalMenuItemData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class HorizontalMenuItemFormType extends AbstractType
{
    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer
     */
    private $removeDuplicatesTransformer;

    /**
     * @var \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer
     */
    private $categoriesIdsToCategoriesTransformer;

    /**
     * @var \App\Model\Category\Category[]
     */
    private $categoryPaths;

    /**
     * @param \Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer
     * @param \Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer,
        CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
        CategoryFacade $categoryFacade
    ) {
        $this->removeDuplicatesTransformer = $removeDuplicatesTransformer;
        $this->categoriesIdsToCategoriesTransformer = $categoriesIdsToCategoriesTransformer;

        $this->categoryPaths = $categoryFacade->getFullPathsIndexedByIdsForDomain(Domain::FIRST_DOMAIN_ID, LocaleHelper::LOCALE_CS);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'horizontal_menu_item_form';
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => t('Název'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Vyplňte prosím název článku']),
                ],
            ])
            ->add('url', TextType::class, [
                'label' => t('URL odkazu'),
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Vyplňte prosím URL odkazu']),
                ],
            ])
            ->add(
                $builder
                    ->create('categoriesInFirstColumn', SortableValuesType::class, [
                        'label' => t('Kategorie prvního sloupce'),
                        'property_path' => 'categoriesByColumnNumber[1]',
                        'labels_by_value' => $this->categoryPaths,
                        'required' => false,
                    ])
                    ->addViewTransformer($this->removeDuplicatesTransformer)
                    ->addModelTransformer($this->categoriesIdsToCategoriesTransformer)
            )
            ->add(
                $builder
                    ->create('categoriesInSecondColumn', SortableValuesType::class, [
                        'label' => t('Kategorie druhého sloupce'),
                        'property_path' => 'categoriesByColumnNumber[2]',
                        'labels_by_value' => $this->categoryPaths,
                        'required' => false,
                    ])
                    ->addViewTransformer($this->removeDuplicatesTransformer)
                    ->addModelTransformer($this->categoriesIdsToCategoriesTransformer)
            )
            ->add(
                $builder
                    ->create('categoriesInThirdColumn', SortableValuesType::class, [
                        'label' => t('Kategorie třetího sloupce'),
                        'property_path' => 'categoriesByColumnNumber[3]',
                        'labels_by_value' => $this->categoryPaths,
                        'required' => false,
                    ])
                    ->addViewTransformer($this->removeDuplicatesTransformer)
                    ->addModelTransformer($this->categoriesIdsToCategoriesTransformer)
            )
            ->add('save', SubmitType::class);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('horizontalMenuItem')
            ->setAllowedTypes('horizontalMenuItem', [HorizontalMenuItem::class, 'null'])
            ->setDefaults([
            'data_class' => HorizontalMenuItemData::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
