<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdminMenuSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_ROOT => 'onConfigureMenu',
        ];
    }

    public function onConfigureMenu(ConfigureMenuEvent $event): void
    {
        if (!$this->authorizationChecker->isGranted(SystemRole::SUPER_ADMIN)) {
            return;
        }

        $mcpServerMenu = $event->getMenu()->addChild('mcp_server', [
            'route' => 'admin_superadmin_mcp_token',
            'label' => t('My MCP server'),
            'display' => false,
        ]);
        // This page only works when the OAuth client sends the required query parameters.
        // Do not generate a menu URI for it; use extras only so breadcrumbs and current-item matching still work.
        $mcpServerAuthorizeMenu = $mcpServerMenu->addChild('mcp_server_authorize', [
            'label' => t('Authorize MCP client'),
            'display' => false,
        ]);
        $mcpServerAuthorizeMenu->setExtra('routes', ['admin_superadmin_mcp_oauth_authorize']);
    }
}
