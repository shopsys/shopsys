<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticle;

/**
 * Testing controller for the CRUD list domain control feature.
 *
 * BlogArticle does not implement DomainSeparatedEntityInterface (it is related
 * to domains through the BlogArticleDomain join entity), so the domain ID field
 * is declared via the $domainIdField argument — the condition is applied as an
 * EXISTS subquery automatically, no configureQuery() override is needed.
 *
 * The quick filter is restricted to domains 1 and 3 to exercise $allowedDomainIds —
 * domain 2 must not be offered, and a selection stored for domain 2 must be cleared.
 */
#[CrudController(BlogArticle::class)]
final class BlogArticleTestController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setMenuTitle(t('Blog articles (domain filter test)'))
            ->setMenuSection(SideMenuBuilder::SECTION_BLOG)
            ->setListDomainControl(
                CrudListDomainControl::QUICK_FILTER,
                allowedDomainIds: [1, 3],
                domainIdField: 'domains.domainId',
            );
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('name', [
                'label' => t('Name'),
            ])
            ->add('createdAt', [
                'label' => t('Created at'),
            ]);
    }
}
