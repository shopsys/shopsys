<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Menu;

use Override;
use Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItem;
use Shopsys\AdministrationBundle\Component\Menu\Dropdown\AdminDropdownMenuItemProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\McpBundle\Component\Availability\McpAvailabilityChecker;
use Shopsys\McpBundle\Component\Routing\McpRouteName;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class McpAdminDropdownMenuItemProvider implements AdminDropdownMenuItemProviderInterface
{
    public function __construct(
        protected readonly AuthorizationCheckerInterface $authorizationChecker,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly McpAvailabilityChecker $mcpAvailabilityChecker,
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

        return [
            new AdminDropdownMenuItem(
                t('My MCP server'),
                'puzzle',
                $this->urlGenerator->generate(McpRouteName::ADMIN_MCP_TOKEN),
                0,
            ),
        ];
    }
}
