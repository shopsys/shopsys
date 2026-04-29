<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use Override;
use PHPUnit\Framework\MockObject\Stub;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\CategoriesType;
use Shopsys\FrameworkBundle\Form\Transformers\CategoriesIdsToCategoriesTransformer;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class CategoriesTypeTest extends TypeTestCase
{
    private const int DOMAIN_ID = 1;
    private const int FIRST_LEVEL_A_CATEGORY_ID = 1;
    private const int A_FIRST_SUBCATEGORY_ID = 2;
    private const int A_SECOND_SUBCATEGORY_ID = 3;
    private const int FIRST_LEVEL_B_CATEGORY_ID = 4;

    private CategoryFacade|Stub $categoryFacade;

    /**
     * @var array<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     */
    private array $categoriesById;

    #[Override]
    protected function setUp(): void
    {
        $this->categoriesById = [
            self::FIRST_LEVEL_A_CATEGORY_ID => $this->createCategoryStub(self::FIRST_LEVEL_A_CATEGORY_ID, 'First level A category', 1, true),
            self::A_FIRST_SUBCATEGORY_ID => $this->createCategoryStub(self::A_FIRST_SUBCATEGORY_ID, 'A first subcategory', 2, false),
            self::A_SECOND_SUBCATEGORY_ID => $this->createCategoryStub(self::A_SECOND_SUBCATEGORY_ID, 'A second subcategory', 2, false),
            self::FIRST_LEVEL_B_CATEGORY_ID => $this->createCategoryStub(self::FIRST_LEVEL_B_CATEGORY_ID, 'First level B category', 1, false),
        ];

        $this->categoryFacade = $this->createStub(CategoryFacade::class);
        $this->categoryFacade->method('getAllCategoriesOfCollapsedTree')->willReturnCallback(
            function (array $categories): array {
                if ($this->hasSelectedSubcategoryOfFirstTopLevelCategory($categories)) {
                    return $this->getExpandedTreeForSelectedSubcategoryOfFirstTopLevelCategory();
                }

                return $this->getCollapsedTreeWithoutSelection();
            },
        );
        $this->categoryFacade->method('getById')->willReturnCallback(
            function (int $categoryId): Category {
                if (!array_key_exists($categoryId, $this->categoriesById)) {
                    throw new CategoryNotFoundException();
                }

                return $this->categoriesById[$categoryId];
            },
        );

        parent::setUp();
    }

    public function testSubmitTransformsCheckedIdsToCategoriesAndExpandsTreeInView(): void
    {
        $form = $this->createCategoriesForm();

        $form->submit([(string)self::A_FIRST_SUBCATEGORY_ID]);
        $view = $form->createView();

        $this->assertSame([$this->categoriesById[self::A_FIRST_SUBCATEGORY_ID]], $form->getData());
        $this->assertSame([self::A_FIRST_SUBCATEGORY_ID], $view->vars['selected_category_ids']);
        $this->assertExpandedTreeForSelectedSubcategoryOfFirstTopLevelCategoryInView($view);
    }

    public function testSubmitWithoutAnyCheckedCategoryKeepsEmptySelection(): void
    {
        $form = $this->createCategoriesForm();

        $form->submit(null);
        $view = $form->createView();

        $this->assertTrue($form->isValid());
        $this->assertSame([], $form->getData());
        $this->assertSame([], $view->vars['selected_category_ids']);
        $this->assertCollapsedTopLevelTreeInView($view);
    }

    public function testSubmitWithNonExistingCategoryIdIsInvalid(): void
    {
        $form = $this->createCategoriesForm();

        $form->submit(['999']);

        $this->assertFalse($form->isValid());
    }

    #[Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    new CategoriesType(
                        new CategoriesIdsToCategoriesTransformer($this->categoryFacade),
                        $this->categoryFacade,
                        $this->createStub(Domain::class),
                        $this->createStub(Localization::class),
                    ),
                ],
                [],
            ),
        ];
    }

    private function createCategoriesForm(): FormInterface
    {
        return $this->factory->create(CategoriesType::class, [], [
            'domain_id' => self::DOMAIN_ID,
        ]);
    }

    private function createCategoryStub(
        int $id,
        string $name,
        int $level,
        bool $hasChildren,
    ): Category {
        $category = $this->createStub(Category::class);
        $category->method('getId')->willReturn($id);
        $category->method('getName')->willReturn($name);
        $category->method('getLevel')->willReturn($level);
        $category->method('isVisible')->willReturn(true);
        $category->method('hasChildren')->willReturn($hasChildren);

        return $category;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     * @return array<int>
     */
    private function getCategoryIdsFromView(array $categories): array
    {
        return array_map(
            fn (Category $category): int => $category->getId(),
            $categories,
        );
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Category\Category> $selectedCategories
     */
    private function hasSelectedSubcategoryOfFirstTopLevelCategory(array $selectedCategories): bool
    {
        return array_intersect(
            $this->getCategoryIdsFromView($selectedCategories),
            [
                self::A_FIRST_SUBCATEGORY_ID,
                self::A_SECOND_SUBCATEGORY_ID,
            ],
        ) !== [];
    }

    /**
     * @return array<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     */
    private function getCollapsedTreeWithoutSelection(): array
    {
        return [
            $this->categoriesById[self::FIRST_LEVEL_A_CATEGORY_ID],
            $this->categoriesById[self::FIRST_LEVEL_B_CATEGORY_ID],
        ];
    }

    /**
     * @return array<int, \Shopsys\FrameworkBundle\Model\Category\Category>
     */
    private function getExpandedTreeForSelectedSubcategoryOfFirstTopLevelCategory(): array
    {
        return [
            $this->categoriesById[self::FIRST_LEVEL_A_CATEGORY_ID],
            $this->categoriesById[self::A_FIRST_SUBCATEGORY_ID],
            $this->categoriesById[self::A_SECOND_SUBCATEGORY_ID],
            $this->categoriesById[self::FIRST_LEVEL_B_CATEGORY_ID],
        ];
    }

    /**
     * @param array<int> $expectedCategoryIds
     */
    private function assertTreeCategoryIds(FormView $view, array $expectedCategoryIds): void
    {
        $this->assertSame(
            $expectedCategoryIds,
            $this->getCategoryIdsFromView($view->vars['tree_categories']),
        );
    }

    private function assertCollapsedTopLevelTreeInView(FormView $view): void
    {
        $this->assertTreeCategoryIds(
            $view,
            [
                self::FIRST_LEVEL_A_CATEGORY_ID,
                self::FIRST_LEVEL_B_CATEGORY_ID,
            ],
        );
    }

    private function assertExpandedTreeForSelectedSubcategoryOfFirstTopLevelCategoryInView(FormView $view): void
    {
        $this->assertTreeCategoryIds(
            $view,
            [
                self::FIRST_LEVEL_A_CATEGORY_ID,
                self::A_FIRST_SUBCATEGORY_ID,
                self::A_SECOND_SUBCATEGORY_ID,
                self::FIRST_LEVEL_B_CATEGORY_ID,
            ],
        );
    }
}
