<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Form;

use Override;
use Shopsys\FrameworkBundle\Form\TreeSelectionType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest\TestTreeSelectionDataProvider;
use Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest\TestTreeSelectionItem;

final class TreeSelectionTypeTest extends TypeTestCase
{
    public const int DOMAIN_ID = 1;
    public const int FIRST_LEVEL_A_ITEM_ID = 1;
    public const int A_FIRST_SUBITEM_ID = 2;
    public const int A_SECOND_SUBITEM_ID = 3;
    public const int FIRST_LEVEL_B_ITEM_ID = 4;

    /**
     * @var array<int, \Tests\FrameworkBundle\Unit\Form\TreeSelectionTypeTest\TestTreeSelectionItem>
     */
    private array $itemsById;

    private TestTreeSelectionDataProvider $treeSelectionDataProvider;

    #[Override]
    protected function setUp(): void
    {
        // TypeTestCase creates a mock dispatcher by default; use a stub to avoid PHPUnit notices.
        $this->dispatcher = $this->createStub(EventDispatcherInterface::class);
        $this->itemsById = [
            self::FIRST_LEVEL_A_ITEM_ID => new TestTreeSelectionItem(self::FIRST_LEVEL_A_ITEM_ID, 'First level A item', 1, true),
            self::A_FIRST_SUBITEM_ID => new TestTreeSelectionItem(self::A_FIRST_SUBITEM_ID, 'A first subitem', 2, false),
            self::A_SECOND_SUBITEM_ID => new TestTreeSelectionItem(self::A_SECOND_SUBITEM_ID, 'A second subitem', 2, false),
            self::FIRST_LEVEL_B_ITEM_ID => new TestTreeSelectionItem(self::FIRST_LEVEL_B_ITEM_ID, 'First level B item', 1, false),
        ];
        $this->treeSelectionDataProvider = new TestTreeSelectionDataProvider($this->itemsById);

        parent::setUp();
    }

    public function testSubmitTransformsCheckedIdsToEntitiesAndExpandsTreeInView(): void
    {
        $form = $this->createTreeSelectionForm();

        $form->submit([(string)self::A_FIRST_SUBITEM_ID]);
        $view = $form->createView();

        $this->assertSame([$this->itemsById[self::A_FIRST_SUBITEM_ID]], $form->getData());
        $this->assertSame([self::A_FIRST_SUBITEM_ID], $view->vars['selected_ids']);
        $this->assertSame('test_branch_load_route', $view->vars['branch_load_route']);
        $this->assertExpandedTreeForSelectedSubitemOfFirstTopLevelItemInView($view);
    }

    public function testSubmitPreservesInputOrderEvenWhenProviderReturnsPlainEntityList(): void
    {
        $form = $this->createTreeSelectionForm();

        $form->submit([
            (string)self::FIRST_LEVEL_B_ITEM_ID,
            (string)self::A_FIRST_SUBITEM_ID,
        ]);

        $this->assertSame(
            [
                $this->itemsById[self::FIRST_LEVEL_B_ITEM_ID],
                $this->itemsById[self::A_FIRST_SUBITEM_ID],
            ],
            $form->getData(),
        );
    }

    public function testSubmitWithoutAnyCheckedItemKeepsEmptySelection(): void
    {
        $form = $this->createTreeSelectionForm();

        $form->submit(null);
        $view = $form->createView();

        $this->assertTrue($form->isValid());
        $this->assertSame([], $form->getData());
        $this->assertSame([], $view->vars['selected_ids']);
        $this->assertCollapsedTopLevelTreeInView($view);
    }

    public function testSubmitWithNullDomainIdKeepsTreeSelectionGeneric(): void
    {
        $form = $this->createTreeSelectionForm(null);
        $form->submit([(string)self::A_FIRST_SUBITEM_ID]);
        $view = $form->createView();

        $this->assertSame([$this->itemsById[self::A_FIRST_SUBITEM_ID]], $form->getData());
        $this->assertNull($view->vars['domain_id']);
    }

    public function testSubmitWithNonExistingItemIdIsInvalid(): void
    {
        $form = $this->createTreeSelectionForm();

        $form->submit(['999']);

        $this->assertFalse($form->isValid());
    }

    #[Override]
    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension(
                [
                    new TreeSelectionType(),
                ],
                [],
            ),
        ];
    }

    private function createTreeSelectionForm(?int $domainId = self::DOMAIN_ID): FormInterface
    {
        return $this->factory->create(TreeSelectionType::class, [], [
            'branch_load_route' => 'test_branch_load_route',
            'domain_id' => $domainId,
            'data_provider' => $this->treeSelectionDataProvider,
        ]);
    }

    private function assertCollapsedTopLevelTreeInView(FormView $view): void
    {
        $treeItems = $view->vars['tree_items'];

        $this->assertCount(2, $treeItems);
        $this->assertSame($this->itemsById[self::FIRST_LEVEL_A_ITEM_ID], $treeItems[0]['item']);
        $this->assertSame([], $treeItems[0]['children']);
        $this->assertSame($this->itemsById[self::FIRST_LEVEL_B_ITEM_ID], $treeItems[1]['item']);
        $this->assertSame([], $treeItems[1]['children']);
    }

    private function assertExpandedTreeForSelectedSubitemOfFirstTopLevelItemInView(FormView $view): void
    {
        $treeItems = $view->vars['tree_items'];

        $this->assertCount(2, $treeItems);
        $this->assertSame($this->itemsById[self::FIRST_LEVEL_A_ITEM_ID], $treeItems[0]['item']);
        $this->assertCount(2, $treeItems[0]['children']);
        $this->assertSame($this->itemsById[self::A_FIRST_SUBITEM_ID], $treeItems[0]['children'][0]['item']);
        $this->assertSame([], $treeItems[0]['children'][0]['children']);
        $this->assertSame($this->itemsById[self::A_SECOND_SUBITEM_ID], $treeItems[0]['children'][1]['item']);
        $this->assertSame([], $treeItems[0]['children'][1]['children']);
        $this->assertSame($this->itemsById[self::FIRST_LEVEL_B_ITEM_ID], $treeItems[1]['item']);
        $this->assertSame([], $treeItems[1]['children']);
    }
}
