<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdminNavigation;

use Knp\Menu\ItemInterface;
use Knp\Menu\Util\MenuManipulator;
use Webmozart\Assert\Assert;

/**
 * Positions a menu item among its siblings.
 *
 * Can be used by any {@see \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent} subscriber to place a
 * freshly added menu item at an exact spot instead of always appending it last.
 *
 * @phpstan-type MenuItemPosition 'first'|'last'|array{before: string}|array{after: string}
 */
class MenuItemPositioner
{
    public function __construct(
        protected readonly MenuManipulator $menuManipulator,
    ) {
    }

    /**
     * Adds a child to the parent menu item and moves it to the requested position among its siblings.
     *
     * Convenience wrapper around {@see \Knp\Menu\ItemInterface::addChild()} and {@see self::moveItemToPosition()} so
     * callers do not have to repeat the add-then-reposition dance.
     *
     * @param array<string, mixed> $options Options passed to {@see \Knp\Menu\ItemInterface::addChild()}
     * @param MenuItemPosition $position
     */
    public function addChild(
        ItemInterface $parent,
        string $name,
        array $options = [],
        string|array $position = 'last',
    ): ItemInterface {
        $item = $parent->addChild($name, $options);
        $this->moveItemToPosition($item, $position);

        return $item;
    }

    /**
     * Moves the given menu item to the requested position among its siblings.
     *
     * KnpMenu always appends new children, so anything other than `'last'` needs an explicit move. The `before`/`after`
     * variants resolve the sibling name to its index first, because KnpMenu only supports moving to a numeric position,
     * not relative to a named sibling. When the referenced sibling is not present, the item is left appended last.
     *
     * @param \Knp\Menu\ItemInterface $item The menu item to position (must already be attached to a parent)
     * @param MenuItemPosition $position
     */
    public function moveItemToPosition(ItemInterface $item, string|array $position): void
    {
        $this->assertValidPosition($position);

        if ($position === 'last') {
            return;
        }

        if ($position === 'first') {
            $this->menuManipulator->moveToFirstPosition($item);

            return;
        }

        $parent = $item->getParent();

        if ($parent === null) {
            return;
        }

        $isBefore = array_key_exists('before', $position);
        $targetName = $isBefore ? $position['before'] : $position['after'];

        $siblingNames = array_values(array_filter(
            array_keys($parent->getChildren()),
            static fn (string $name): bool => $name !== $item->getName(),
        ));
        $targetIndex = array_search($targetName, $siblingNames, true);

        if ($targetIndex === false) {
            return;
        }

        $this->menuManipulator->moveToPosition($item, $isBefore ? $targetIndex : $targetIndex + 1);
    }

    /**
     * @param MenuItemPosition $position
     */
    protected function assertValidPosition(string|array $position): void
    {
        if (is_string($position)) {
            Assert::oneOf(
                $position,
                ['first', 'last'],
                'Menu item position must be "first", "last", or an array with a single "before" or "after" key, got %s.',
            );

            return;
        }

        Assert::count(
            $position,
            1,
            'Menu item position array must contain exactly one "before" or "after" key.',
        );

        $key = array_key_first($position);
        Assert::oneOf(
            $key,
            ['before', 'after'],
            'Menu item position array must use a "before" or "after" key, got "%s".',
        );
        Assert::string(
            $position[$key],
            'Menu item position target (sibling name) must be a string.',
        );
    }
}
