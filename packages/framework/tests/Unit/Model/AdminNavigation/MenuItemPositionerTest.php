<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\AdminNavigation;

use InvalidArgumentException;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\Util\MenuManipulator;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\AdminNavigation\MenuItemPositioner;

class MenuItemPositionerTest extends TestCase
{
    private function createPositioner(): MenuItemPositioner
    {
        return new MenuItemPositioner(new MenuManipulator());
    }

    /**
     * @param string[] $childNames
     */
    private function createMenuWithChildren(array $childNames): ItemInterface
    {
        $root = (new MenuFactory())->createItem('root');

        foreach ($childNames as $name) {
            $root->addChild($name);
        }

        return $root;
    }

    public function testAddChildAppendsLastByDefaultAndReturnsAddedChild(): void
    {
        $root = $this->createMenuWithChildren(['a', 'b']);

        $child = $this->createPositioner()->addChild($root, 'c');

        $this->assertSame('c', $child->getName());
        $this->assertSame(['a', 'b', 'c'], array_keys($root->getChildren()));
    }

    public function testAddChildAppliesPositionInSingleCall(): void
    {
        $root = $this->createMenuWithChildren(['a', 'b']);

        $this->createPositioner()->addChild($root, 'c', [], ['after' => 'a']);

        $this->assertSame(['a', 'c', 'b'], array_keys($root->getChildren()));
    }

    public function testMoveToFirstPosition(): void
    {
        $root = $this->createMenuWithChildren(['a', 'b', 'c']);
        $item = $root->addChild('x');

        $this->createPositioner()->moveItemToPosition($item, 'first');

        $this->assertSame(['x', 'a', 'b', 'c'], array_keys($root->getChildren()));
    }

    public function testMoveToLastPositionKeepsItemLast(): void
    {
        $root = $this->createMenuWithChildren(['a', 'b']);
        $item = $root->addChild('x');

        $this->createPositioner()->moveItemToPosition($item, 'last');

        $this->assertSame(['a', 'b', 'x'], array_keys($root->getChildren()));
    }

    public function testMoveBeforeSibling(): void
    {
        $root = $this->createMenuWithChildren(['a', 'b', 'c']);
        $item = $root->addChild('x');

        $this->createPositioner()->moveItemToPosition($item, ['before' => 'b']);

        $this->assertSame(['a', 'x', 'b', 'c'], array_keys($root->getChildren()));
    }

    public function testMoveAfterSiblingInTheMiddle(): void
    {
        $root = $this->createMenuWithChildren(['a', 'b', 'c']);
        $item = $root->addChild('x');

        $this->createPositioner()->moveItemToPosition($item, ['after' => 'b']);

        $this->assertSame(['a', 'b', 'x', 'c'], array_keys($root->getChildren()));
    }

    public function testMoveAfterLastSiblingKeepsItemLast(): void
    {
        $root = $this->createMenuWithChildren(['a', 'b', 'c']);
        $item = $root->addChild('x');

        $this->createPositioner()->moveItemToPosition($item, ['after' => 'c']);

        $this->assertSame(['a', 'b', 'c', 'x'], array_keys($root->getChildren()));
    }

    public function testMissingTargetLeavesItemAppendedLast(): void
    {
        $root = $this->createMenuWithChildren(['a', 'b', 'c']);
        $item = $root->addChild('x');

        $this->createPositioner()->moveItemToPosition($item, ['after' => 'does_not_exist']);

        $this->assertSame(['a', 'b', 'c', 'x'], array_keys($root->getChildren()));
    }

    public function testInvalidStringPositionThrowsException(): void
    {
        $root = $this->createMenuWithChildren(['a']);
        $item = $root->addChild('x');

        $this->expectException(InvalidArgumentException::class);

        $this->createPositioner()->moveItemToPosition($item, 'middle'); // @phpstan-ignore argument.type
    }

    public function testArrayWithUnknownKeyThrowsException(): void
    {
        $root = $this->createMenuWithChildren(['a']);
        $item = $root->addChild('x');

        $this->expectException(InvalidArgumentException::class);

        $this->createPositioner()->moveItemToPosition($item, ['around' => 'a']); // @phpstan-ignore argument.type
    }

    public function testArrayWithMultipleKeysThrowsException(): void
    {
        $root = $this->createMenuWithChildren(['a', 'b']);
        $item = $root->addChild('x');

        $this->expectException(InvalidArgumentException::class);

        $this->createPositioner()->moveItemToPosition($item, ['before' => 'a', 'after' => 'b']);
    }

    public function testArrayWithNonStringTargetThrowsException(): void
    {
        $root = $this->createMenuWithChildren(['a']);
        $item = $root->addChild('x');

        $this->expectException(InvalidArgumentException::class);

        $this->createPositioner()->moveItemToPosition($item, ['before' => 123]); // @phpstan-ignore argument.type
    }
}
