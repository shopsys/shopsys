<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Category\Category;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\DomainFilterMode;
use Shopsys\AdministrationBundle\Component\Crud\Domain\CrudDomainFilterResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;

/**
 * Manual test controller: COLLECTION entity with the per-page FILTER mode override.
 * Expected: quick domain filter above the grid with an "all domains" option (DomainFilterMode::FILTER).
 * "all domains" (null) => no domain row joined, only id/name shown; a selected domain => the domain row is
 * LEFT JOINed under CrudDomainFilterResolver::DOMAIN_JOIN_ALIAS and the per-domain "visible" column appears.
 */
#[CrudController(Category::class)]
final class CrudDomainFilterModeTestController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setMenuTitle(t('TEST domain filter: COLLECTION + FILTER (Category)'))
            ->setDomainFilterMode(DomainFilterMode::FILTER);
    }

    #[Override]
    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
        if ($this->selectedDomainFilterId !== null) {
            $queryBuilder->addSelect(CrudDomainFilterResolver::DOMAIN_JOIN_ALIAS . '.visible AS domainVisible');
        }
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('id', [
                'label' => t('Id'),
            ])
            ->add('name', [
                'label' => t('Name'),
            ]);

        if ($this->selectedDomainFilterId !== null) {
            $datagrid->add('domainVisible', [
                'label' => t('Visible on selected domain'),
                'virtual' => true,
                'property' => 'domainVisible',
            ]);
        }
    }
}
