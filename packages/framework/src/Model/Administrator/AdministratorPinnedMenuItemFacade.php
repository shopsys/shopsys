<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;

class AdministratorPinnedMenuItemFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorPinnedMenuItemFactory $administratorPinnedMenuItemFactory,
    ) {
    }

    protected function pinMenuItem(Administrator $administrator, string $routeName): void
    {
        $pinnedMenuItem = $this->administratorPinnedMenuItemFactory->create(
            $administrator,
            $routeName,
            $this->getNextPinnedMenuItemPosition($administrator),
        );
        $administrator->addPinnedMenuItem($pinnedMenuItem);
        $this->em->flush();
    }

    protected function getNextPinnedMenuItemPosition(Administrator $administrator): int
    {
        $maxPosition = -1;

        foreach ($administrator->getPinnedMenuItems() as $pinnedMenuItem) {
            $maxPosition = max($maxPosition, $pinnedMenuItem->getPosition());
        }

        return $maxPosition + 1;
    }

    protected function unpinMenuItem(Administrator $administrator, string $routeName): void
    {
        $administrator->unpinMenuItemByRouteName($routeName);
        $this->em->flush();
    }

    public function toggleMenuItem(Administrator $administrator, string $routeName): bool
    {
        if ($administrator->isMenuItemPinned($routeName)) {
            $this->unpinMenuItem($administrator, $routeName);

            return false;
        }

        $this->pinMenuItem($administrator, $routeName);

        return true;
    }

    /**
     * @param string[] $orderedRouteNames
     */
    public function reorderPinnedMenuItems(Administrator $administrator, array $orderedRouteNames): void
    {
        $administrator->reorderPinnedMenuItems($orderedRouteNames);
        $this->em->flush();
    }
}
