<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Admin\Navigation;

use Override;
use Shopsys\FormTypesBundle\ActionBarType;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\DomainType;
use Shopsys\FrameworkBundle\Form\SortableValuesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Form\Transformers\RemoveDuplicatesFromArrayTransformer;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItem;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemData;
use Shopsys\FrameworkBundle\Model\Navigation\NavigationItemTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class NavigationItemFormType extends AbstractType
{
    public function __construct(
        private readonly RemoveDuplicatesFromArrayTransformer $removeDuplicatesTransformer,
        private readonly CategoriesIdsToCategoriesTransformer $categoriesIdsToCategoriesTransformer,
        private readonly CategoryFacade $categoryFacade,
        private readonly Localization $localization,
        private readonly NavigationItemTypeEnum $navigationItemTypeEnum,
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
                    new Constraints\NotBlank(message: 'Please enter navigation item name'),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Navigation item type',
                'required' => true,
                'expanded' => true,
                'choices' => $this->navigationItemTypeEnum->getAllIndexedByTranslations(),
                'choice_attr' => static function ($choice, $key, $value): array {
                    return [
                        'class' => 'js-navigation-item-type',
                    ];
                },
                'constraints' => [
                    new Constraints\NotBlank(message: 'Please select navigation item type'),
                    new Constraints\Choice(
                        choices: $this->navigationItemTypeEnum->getAllCases(),
                        message: 'Please select valid navigation item type',
                    ),
                ],
                'attr' => [
                    'class' => 'js-navigation-item-type',
                ],
            ])
            ->add('url', TextType::class, [
                'label' => 'Link URL',
                'help' => t('Supported formats: /url-address, url-address, https://example.com/url-address'),
                'required' => false,
                'row_attr' => [
                    'class' => 'js-navigation-item-link-field',
                ],
            ]);
        $this->addColumnFields($builder);
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event): void {
            $navigationItemData = $event->getData();

            if (!$navigationItemData instanceof NavigationItemData) {
                return;
            }

            if ($navigationItemData->type === NavigationItemTypeEnum::LINK) {
                $navigationItemData->categoriesByColumnNumber = [];
            }

            if ($navigationItemData->type === NavigationItemTypeEnum::CATEGORIES) {
                $navigationItemData->url = null;
            }
        });
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
                'constraints' => [
                    new Constraints\Callback([$this, 'validateNavigationItemData']),
                ],
            ]);
    }

    public function validateNavigationItemData(
        NavigationItemData $navigationItemData,
        ExecutionContextInterface $context,
    ): void {
        if (
            $navigationItemData->type === NavigationItemTypeEnum::LINK
            && ($navigationItemData->url === null || trim($navigationItemData->url) === '')
        ) {
            $context->buildViolation(t('Please enter link URL', domain: Translator::VALIDATOR_TRANSLATION_DOMAIN))
                ->atPath('url')
                ->addViolation();

            return;
        }

        if (
            $navigationItemData->type === NavigationItemTypeEnum::CATEGORIES
            && !$this->hasCategories($navigationItemData)
        ) {
            $context->buildViolation(t('Please select at least one category', domain: Translator::VALIDATOR_TRANSLATION_DOMAIN))
                ->atPath('type')
                ->addViolation();
        }
    }

    private function hasCategories(NavigationItemData $navigationItemData): bool
    {
        foreach ($navigationItemData->categoriesByColumnNumber as $categories) {
            if (count($categories) > 0) {
                return true;
            }
        }

        return false;
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
                'row_attr' => [
                    'class' => 'js-navigation-item-categories-field',
                ],
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
