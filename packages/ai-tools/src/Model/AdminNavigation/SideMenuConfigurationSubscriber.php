<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Model\AdminNavigation;

use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SideMenuConfigurationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ConfigureMenuEvent::SIDE_MENU_INTEGRATIONS => 'configureIntegrationMenu',
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureIntegrationMenu(ConfigureMenuEvent $event): void
    {
        $integrationsMenu = $event->getMenu();
        // AI section
        $aiMenu = $integrationsMenu->addChild('ai', ['label' => t('Ai')]);
        $vectorStoreMenu = $aiMenu->addChild('vectorStores', ['route' => 'shopsys_aitools_admin_vectorstore_list', 'label' => t('Vector stores')]);
        $vectorStoreMenu->addChild('new_vector_store', ['route' => 'shopsys_aitools_admin_vectorstore_new', 'display' => false, 'label' => t('New vector store')]);
        $vectorStoreMenu->addChild('edit_vector_store', ['route' => 'shopsys_aitools_admin_vectorstore_edit', 'display' => false, 'label' => t('Agent vector store')]);

        $agentMenu = $aiMenu->addChild('agents', ['route' => 'shopsys_aitools_admin_agent_list', 'label' => t('Agents')]);
        $agentMenu->addChild('new_agent', ['route' => 'shopsys_aitools_admin_agent_new', 'display' => false, 'label' => t('New agent')]);
        $agentMenu->addChild('edit_agent', ['route' => 'shopsys_aitools_admin_agent_edit', 'display' => false, 'label' => t('Agent detail')]);

        $chatMenu = $aiMenu->addChild('chats', ['route' => 'shopsys_aitools_admin_chat_list', 'label' => t('Chats')]);
        $chatMenu->addChild('edit_chat', ['route' => 'shopsys_aitools_admin_chat_edit', 'display' => false, 'label' => t('Chat detail')]);
        $chatMenu->addChild('new_chat', ['route' => 'shopsys_aitools_admin_chat_new', 'display' => false, 'label' => t('New chat ')]);

        $aiModels = $aiMenu->addChild('ai_models', ['route' => 'shopsys_aitools_admin_aimodel_list', 'label' => t('AI models')]);
        $aiModels->addChild('edit_ai_models', ['route' => 'shopsys_aitools_admin_aimodel_edit', 'display' => false, 'label' => t('AI model detail')]);
    }
}
