<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BlogSideMenuConfigurationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [ConfigureMenuEvent::SIDE_MENU_MARKETING => 'configureBlogMenus'];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\ConfigureMenuEvent $event
     */
    public function configureBlogMenus(ConfigureMenuEvent $event): void
    {
        $menu = $event->getMenu();

        $menu->addChild('blog', ['label' => t('Blog')]);
        $blogMenu = $menu->getChild('blog');

        $blogMenu->addChild('blogCategories', ['route' => 'admin_blogcategory_list', 'label' => t('Rubriky blogu')]);

        $blogCategories = $blogMenu->getChild('blogCategories');
        $blogCategories->addChild('newBlogCategories', ['route' => 'admin_blogcategory_new', 'display' => false, 'label' => t('Nová rubrika blogu')]);
        $blogCategories->addChild('editBlogCategories', ['route' => 'admin_blogcategory_edit', 'display' => false]);

        $blogMenu->addChild('blogArticles', ['route' => 'admin_blogarticle_list', 'label' => t('Články blogu')]);

        $blogArticles = $blogMenu->getChild('blogArticles');
        $blogArticles->addChild('newBlogArticles', ['route' => 'admin_blogarticle_new', 'display' => false, 'label' => t('Nový článek blogu')]);
        $blogArticles->addChild('editBlogArticles', ['route' => 'admin_blogarticle_edit', 'display' => false]);
    }
}
