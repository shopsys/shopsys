<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Menu\Dropdown;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('shopsys.admin.dropdown_menu_item_provider')]
interface AdminDropdownMenuItemProviderInterface
{
    /**
     * @return \Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItem[]
     */
    public function getItems(): array;
}
