<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Model\AdditionalService\AdditionalServiceCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\AdditionalService\AdditionalServiceFormType;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;

#[CrudController(AdditionalService::class)]
#[ForRole(AdminRoleConstant::ROLE_ADDITIONAL_SERVICE)]
class AdditionalServiceController extends AbstractCrudController
{
    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setMenuSection(
                SideMenuBuilder::ROOT_PRODUCT,
                null,
                ['after' => SideMenuBuilder::LIST_CATEGORY],
            )
            ->registerHandler(AdditionalServiceCrudHandler::class);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('name', [
                'label' => t('Name'),
            ])
            ->add('catnum', [
                'label' => t('Catalog number'),
            ]);

        $datagrid->enableDragAndDrop('position');
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(AdditionalServiceFormType::class, [
            'additionalService' => $entity,
        ]);
    }
}
