<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu\Dropdown;

use Twig\Environment;

class AdminDropdownMenuItemsRenderer
{
    /**
     * @param iterable<\Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItemProviderInterface> $adminDropdownMenuItemProviders
     */
    public function __construct(
        protected readonly iterable $adminDropdownMenuItemProviders,
        protected readonly Environment $twigEnvironment,
    ) {
    }

    public function render(): string
    {
        return $this->twigEnvironment->render('@ShopsysAdministration/partial/admin_dropdown_menu_items.html.twig', [
            'items' => $this->getItems(),
        ]);
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItem[]
     */
    protected function getItems(): array
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
