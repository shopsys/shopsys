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

        $superadminMenu->addChild('mcp_server', [
            'route' => 'admin_superadmin_mcp_token',
            'label' => t('MCP server'),
        ]);
    }
}
