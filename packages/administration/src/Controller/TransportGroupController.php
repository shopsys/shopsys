<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Search\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;
use Shopsys\AdministrationBundle\Model\Transport\TransportGroupCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportGroupFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Transport\TransportGroup;

#[CrudController(TransportGroup::class)]
class TransportGroupController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setRoutePrefix('/transport-and-payment')
            ->setMenuSection(
                SideMenuBuilder::ROOT_SETTING,
                SideMenuBuilder::SECTION_LISTS,
                ['after' => SideMenuBuilder::LIST_TRANSPORT_AND_PAYMENT],
            )
            ->setCustomRoleConstant(AdminRoleConstant::ROLE_TRANSPORT_AND_PAYMENT)
            ->registerHandler(TransportGroupCrudHandler::class);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('name', [
                'label' => t('Name'),
            ]);

        $datagrid->enableDragAndDrop('position');
    }

    #[Override]
    public function configureSearch(SearchConfig $search): void
    {
        $search->enableQuickSearch(
            fields: ['name'],
            placeholder: t('Search by name…'),
        );

        $search->addFilter(TextFilter::create('name', t('Name')));
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(TransportGroupFormType::class, [
            'transportGroup' => $entity,
        ]);
    }
}
