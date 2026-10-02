<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\Transform\BatchLoadedRowTransformFactory;
use Shopsys\AdministrationBundle\Model\Advert\AdvertCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Shopsys\FrameworkBundle\Form\Admin\Advert\AdvertFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Advert\Advert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Shopsys\FrameworkBundle\Twig\ImageExtension;
use SortDirection;

#[CrudController(Advert::class)]
#[ForRole(AdminRoleConstant::ROLE_ADVERT)]
class AdvertController extends AbstractCrudController
{
    public function __construct(
        protected readonly AdvertFacade $advertFacade,
        protected readonly AdvertPositionRegistry $advertPositionRegistry,
        protected readonly ImageExtension $imageExtension,
        protected readonly BatchLoadedRowTransformFactory $batchLoadedRowTransformFactory,
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
    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->addSelect('CASE WHEN o.hidden = FALSE THEN TRUE ELSE FALSE END AS visibility');
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $advertPositionNames = $this->advertPositionRegistry->getAllLabelsIndexedByNames();

        $datagrid
            ->add('visible', [
                'label' => t('Visibility'),
                'virtual' => true,
                'property' => 'visibility',
            ])
            ->add('name', [
                'label' => t('Name'),
            ])
            ->add('preview', [
                'label' => t('Preview'),
                'virtual' => true,
                'transform' => $this->batchLoadedRowTransformFactory->createFromEntitiesLoader($this->advertFacade->getByIds(...)),
                'template' => '@ShopsysAdministration/content/advert/grid/preview.html.twig',
            ])
            ->add('positionName', [
                'label' => t('Area'),
                'transform' => fn (mixed $positionName): string => $advertPositionNames[$positionName] ?? (string)$positionName,
            ]);

        $datagrid->setDefaultOrder('name', SortDirection::Ascending);
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?Presentable $entity = null): void
    {
        $formConfigurator->useFormType(AdvertFormType::class, [
            'scenario' => $entity === null ? AdvertFormType::SCENARIO_CREATE : AdvertFormType::SCENARIO_EDIT,
            'advert' => $entity,
            'web_image_exists' => $entity !== null && $this->imageExtension->imageExists($entity, AdvertFacade::IMAGE_TYPE_WEB),
            'mobile_image_exists' => $entity !== null && $this->imageExtension->imageExists($entity, AdvertFacade::IMAGE_TYPE_MOBILE),
        ]);
    }
}
