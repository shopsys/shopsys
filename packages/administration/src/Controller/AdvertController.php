<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Action\RowAction;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\OrderingEnum;
use Shopsys\AdministrationBundle\Model\Advert\AdvertCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Shopsys\FrameworkBundle\Twig\ImageExtension;

#[CrudController(Advert::class)]
#[ForRole(AdminRoleConstant::ROLE_ADVERT)]
class AdvertController extends AbstractCrudController
{
    public function __construct(
        protected readonly AdvertFacade $advertFacade,
        protected readonly AdvertPositionRegistry $advertPositionRegistry,
        protected readonly ImageExtension $imageExtension,
    ) {
    }

    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setEntityNameSingular(t('Advertising'))
            ->setEntityNamePlural(t('Advertising system'))
            ->setMenuSection(SideMenuBuilder::ROOT_CMS, null, ['after' => SideMenuBuilder::LIST_ARTICLE])
            ->setListDomainControl(CrudListDomainControl::SWITCHER)
            ->registerHandler(AdvertCrudHandler::class);
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $advertPositionNames = $this->advertPositionRegistry->getAllLabelsIndexedByNames();

        $datagrid
            ->add('hidden', [
                'visible' => false,
            ])
            ->add('visible', [
                'label' => t('Visibility'),
                'virtual' => true,
                'transform' => fn (mixed $value, array $row): bool => !$row['hidden'],
            ])
            ->add('name', [
                'label' => t('Name'),
            ])
            ->add('type', [
                'visible' => false,
            ])
            ->add('preview', [
                'label' => t('Preview'),
                'virtual' => true,
                'transform' => fn (mixed $value, array $row): Advert => $this->advertFacade->getById((int)$row['id']),
                'template' => '@ShopsysAdministration/content/advert/grid/preview.html.twig',
            ])
            ->add('positionName', [
                'label' => t('Area'),
                'transform' => fn (mixed $positionName): string => $advertPositionNames[$positionName] ?? (string)$positionName,
            ]);

        $datagrid->setDefaultOrder('name', OrderingEnum::ASC);

        $datagrid->actions()->update(
            'delete',
            fn (RowAction $rowAction) => $rowAction->setConfirmMessage(t('Do you really want to remove this advert?')),
        );
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(AdvertFormType::class, [
            'scenario' => $entity === null ? AdvertFormType::SCENARIO_CREATE : AdvertFormType::SCENARIO_EDIT,
            'advert' => $entity,
            'web_image_exists' => $entity !== null && $this->imageExtension->imageExists($entity, AdvertFacade::IMAGE_TYPE_WEB),
            'mobile_image_exists' => $entity !== null && $this->imageExtension->imageExists($entity, AdvertFacade::IMAGE_TYPE_MOBILE),
        ]);
    }
}
