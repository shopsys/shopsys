<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\EventSubscriber;

use Override;
use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminMenuSubscriber implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_SETTINGS => 'onConfigureMenu',
        ];
    }

    public function onConfigureMenu(ConfigureMenuEvent $event): void
    {
        $superadminMenu = $event->getMenu()->getChild(SideMenuBuilder::SECTION_SUPERADMIN);

        if ($superadminMenu === null) {
            return;
        }

        $mcpServerMenu = $superadminMenu->addChild('mcp_server', [
            'route' => 'admin_superadmin_mcp_token',
            'label' => t('MCP server'),
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
