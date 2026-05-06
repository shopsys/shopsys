<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Knp\Menu\Matcher\MatcherInterface;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorPinnedMenuItemFacade;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class PinnedMenuItemController extends AdminBaseController
{
    public function __construct(
        protected readonly AdministratorPinnedMenuItemFacade $administratorPinnedMenuItemFacade,
        protected readonly SideMenuBuilder $sideMenuBuilder,
        protected readonly MatcherInterface $matcher,
    ) {
    }

    #[Route(path: '/pinned-menu-item/toggle/', methods: ['POST'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function toggleAction(Request $request): JsonResponse
    {
        $routeName = $request->request->getString('routeName');

        $menu = $this->sideMenuBuilder->createMenu();

        if (!$this->sideMenuBuilder->isRouteNamePinnable($menu, $routeName)) {
            throw new BadRequestHttpException(sprintf('Route "%s" is not a pinnable menu item.', $routeName));
        }

        $pinned = $this->administratorPinnedMenuItemFacade->toggleMenuItem(
            $this->getCurrentAdministrator(),
            $routeName,
        );

        $menu = $this->sideMenuBuilder->createMenu();

        return new JsonResponse([
            'pinned' => $pinned,
            'pinnedSectionHtml' => $this->renderView(
                '@ShopsysAdministration/partial/_pinned_section.html.twig',
                [
                    'item' => $menu->getChild(SideMenuBuilder::ROOT_PINNED),
                    'matcher' => $this->matcher,
                ],
            ),
        ]);
    }

    #[Route(path: '/pinned-menu-item/reorder/', methods: ['POST'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function reorderAction(Request $request): JsonResponse
    {
        /** @var string[] $orderedPaths */
        $orderedPaths = $request->request->all('orderedPaths');

        $this->administratorPinnedMenuItemFacade->reorderPinnedMenuItems(
            $this->getCurrentAdministrator(),
            $orderedPaths,
        );

        return new JsonResponse(['success' => true]);
    }
}
