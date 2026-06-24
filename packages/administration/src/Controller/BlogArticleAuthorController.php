<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\OrderingEnum;
use Shopsys\AdministrationBundle\Model\Blog\Author\BlogArticleAuthorCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Blog\BlogArticleAuthorFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthor;

#[CrudController(BlogArticleAuthor::class)]
class BlogArticleAuthorController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->registerHandler(BlogArticleAuthorCrudHandler::class)
            ->setMenuSection(SideMenuBuilder::SECTION_BLOG)
            ->setCustomRoleConstant(AdminRoleConstant::ROLE_BLOG_ARTICLE_AUTHOR);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid->add('name', [
            'label' => t('Name'),
        ]);

        $datagrid->setDefaultOrder('name', OrderingEnum::ASC);
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(BlogArticleAuthorFormType::class, [
            'blogArticleAuthor' => $entity,
        ]);
    }
}
