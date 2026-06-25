<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Model\Category\Category;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Domain\CrudDomainFilterResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;

/**
 * Manual test controller: entity with a `domains` OneToMany association (CategoryDomain has a `domainId`).
 * Expected: global domain switcher above the grid (DomainFilterMode::SWITCH default), DomainFilterType::COLLECTION.
 * Rows are NOT filtered (every category exists on every domain); the selected domain's row is LEFT JOINed
 * under CrudDomainFilterResolver::DOMAIN_JOIN_ALIAS so we can show its per-domain "visible" flag.
 */
#[CrudController(Category::class)]
final class CrudDomainCollectionTestController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config->setMenuTitle(t('TEST domain filter: COLLECTION + SWITCH (Category)'));
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
