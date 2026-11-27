<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu\Dropdown;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Twig\Environment;

class AdminDropdownMenuItemsRenderer
{
    protected const string ITEMS_CACHE_NAMESPACE = 'adminDropdownMenuItems';

    /**
     * @param iterable<\Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItemProviderInterface> $adminDropdownMenuItemProviders
     */
    public function __construct(
        protected readonly iterable $adminDropdownMenuItemProviders,
        protected readonly Environment $twigEnvironment,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function render(): string
    {
        return $this->twigEnvironment->render('@ShopsysAdministration/partial/admin_dropdown_menu_items.html.twig', [
            'items' => $this->getItems(),
        ]);
    }

    public function getBadgeCountSum(): int
    {
        $badgeCountSum = 0;

        foreach ($this->getItems() as $adminDropdownMenuItem) {
            $badgeCountSum += $adminDropdownMenuItem->badgeCount ?? 0;
        }

        return $badgeCountSum;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItem[]
     */
    protected function getItems(): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::ITEMS_CACHE_NAMESPACE,
            fn (): array => $this->loadItems(),
            'items',
        );
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItem[]
     */
    protected function loadItems(): array
    {
        $items = [];

        foreach ($this->adminDropdownMenuItemProviders as $adminDropdownMenuItemProvider) {
            foreach ($adminDropdownMenuItemProvider->getItems() as $adminDropdownMenuItem) {
                $items[] = $adminDropdownMenuItem;
            }
        }

        usort(
            $items,
            static fn (AdminDropdownMenuItem $firstItem, AdminDropdownMenuItem $secondItem): int => $secondItem->priority <=> $firstItem->priority,
        );

        return $items;
    }
}
