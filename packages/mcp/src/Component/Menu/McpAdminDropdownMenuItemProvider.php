<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Menu;

use Override;
use Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItem;
use Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItemProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Routing\McpRouteName;
use Shopsys\McpBundle\Model\ChangeRequest\McpChangeRequestFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class McpAdminDropdownMenuItemProvider implements AdminDropdownMenuItemProviderInterface
{
    public function __construct(
        protected readonly AuthorizationCheckerInterface $authorizationChecker,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
        protected readonly Security $security,
        protected readonly McpChangeRequestFacade $mcpChangeRequestFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getItems(): array
    {
        if (!$this->mcpAvailabilityChecker->isAvailable() || !$this->authorizationChecker->isGranted(SystemRole::SUPER_ADMIN)) {
            return [];
        }

        $items = [
            new AdminDropdownMenuItem(
                t('My MCP server'),
                'puzzle',
                $this->urlGenerator->generate(McpRouteName::ADMIN_MCP_TOKEN),
                0,
            ),
        ];

        $administrator = $this->security->getUser();
        $openChangeRequestsCount = $administrator instanceof Administrator ? $this->mcpChangeRequestFacade->countOpenByAdministrator($administrator) : 0;

        if ($openChangeRequestsCount > 0) {
            $items[] = new AdminDropdownMenuItem(
                t('MCP change requests'),
                'checked-circle',
                $this->urlGenerator->generate(McpRouteName::ADMIN_MCP_CHANGE_REQUEST_LIST),
                1,
                badgeCount: $openChangeRequestsCount,
            );
        }

        return $items;
    }
}
