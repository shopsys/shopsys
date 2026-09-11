<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Override;
use Psr\Clock\ClockInterface;
use Shopsys\AdministrationBundle\Component\Action\RowAction;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\OrderingEnum;
use Shopsys\AdministrationBundle\Model\NotificationBar\NotificationBarCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\NotificationBar\NotificationBarFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar;

#[CrudController(NotificationBar::class)]
#[ForRole(AdminRoleConstant::ROLE_NOTIFICATION_BAR)]
class NotificationBarController extends AbstractCrudController
{
    public function __construct(
        protected readonly ClockInterface $clock,
    ) {
    }

    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setMenuTitle(t('Notification bar'))
            ->setMenuSection(SideMenuBuilder::ROOT_CMS, null, ['after' => SideMenuBuilder::AUTOCOMPLETE_SETTING])
            ->setListDomainControl(CrudListDomainControl::SWITCHER)
            ->registerHandler(NotificationBarCrudHandler::class);
    }

    #[Override]
    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->addSelect('CASE WHEN (o.hidden = FALSE AND (o.validityFrom IS NULL OR o.validityFrom <= :now) AND (o.validityTo IS NULL OR o.validityTo > :now)) THEN TRUE ELSE FALSE END AS visibility')
            ->setParameter('now', $this->clock->now());
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('visible', [
                'label' => t('Visibility'),
                'virtual' => true,
                'property' => 'visibility',
            ])
            ->add('text', [
                'label' => t('Text'),
                'template' => '@ShopsysAdministration/content/notificationBar/grid/text.html.twig',
            ])
            ->add('validityFrom', [
                'label' => t('Valid from'),
                'template' => '@ShopsysAdministration/content/notificationBar/grid/validity.html.twig',
            ])
            ->add('validityTo', [
                'label' => t('Valid to'),
                'template' => '@ShopsysAdministration/content/notificationBar/grid/validity.html.twig',
            ]);

        $datagrid->setDefaultOrder('id', OrderingEnum::ASC);

        $datagrid->actions()->update(
            'delete',
            fn (RowAction $rowAction) => $rowAction->setConfirmMessage(t('Do you really want to remove this notification bar?')),
        );
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(NotificationBarFormType::class, [
            'scenario' => $entity === null ? NotificationBarFormType::SCENARIO_CREATE : NotificationBarFormType::SCENARIO_EDIT,
            'notification_bar' => $entity,
        ]);
    }
}
