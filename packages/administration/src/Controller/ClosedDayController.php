<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Override;
use Shopsys\AdministrationBundle\Component\Action\Action;
use Shopsys\AdministrationBundle\Component\Action\RowAction;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Component\Config\ActionsConfig;
use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\AdministrationBundle\Component\Config\CrudConfig;
use Shopsys\AdministrationBundle\Component\Config\CrudListDomainControl;
use Shopsys\AdministrationBundle\Component\Crud\Form\CrudFormConfigurator;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\OrderingEnum;
use Shopsys\AdministrationBundle\Model\Store\ClosedDay\ClosedDayCrudHandler;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Holidays\HolidaysImportFormType;
use Shopsys\FrameworkBundle\Form\Admin\Store\ClosedDayFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\Holiday\HolidaysImportDataFactory;
use Shopsys\FrameworkBundle\Model\Holiday\HolidaysImportFacade;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay;
use Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayFacade;
use Spatie\Holidays\Exceptions\InvalidCountry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[CrudController(ClosedDay::class)]
#[ForRole(AdminRoleConstant::ROLE_CLOSED_DAYS)]
class ClosedDayController extends AbstractCrudController
{
    public function __construct(
        protected readonly ClosedDayFacade $closedDayFacade,
        protected readonly HolidaysImportDataFactory $holidaysImportDataFactory,
        protected readonly HolidaysImportFacade $holidaysImportFacade,
    ) {
    }

    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setEntityNameSingular(t('Holiday / internal day'))
            ->setEntityNamePlural(t('Holidays and internal days'))
            ->setMenuSection(SideMenuBuilder::ROOT_SETTING, SideMenuBuilder::SECTION_LISTS)
            ->setListDomainControl(CrudListDomainControl::SWITCHER)
            ->registerHandler(ClosedDayCrudHandler::class);
    }

    #[Override]
    protected function configureActions(ActionsConfig $actions): void
    {
        $actions->add(
            ActionType::LIST,
            Action::create('holidaysImport', t('Import holidays'))
                ->setIcon('download')
                ->setAttribute('class', 'btn-secondary', true)
                ->linkToRoute('admin_crud_closed_day_holidays_import'),
        );
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('name', [
                'label' => t('Name'),
            ])
            ->add('date', [
                'label' => t('Date'),
                'template' => '@ShopsysAdministration/content/closedDay/grid/date.html.twig',
            ])
            ->add('isPublicHoliday', [
                'label' => t('Public holiday'),
            ])
            ->add('excludedStores', [
                'label' => t('Excluded stores'),
                'virtual' => true,
                'transform' => fn (mixed $value, array $row): array => $this->closedDayFacade->getById((int)$row['id'])->getExcludedStores(),
                'template' => '@ShopsysAdministration/content/closedDay/grid/excludedStores.html.twig',
            ]);

        $datagrid->setDefaultOrder('date', OrderingEnum::ASC);

        $datagrid->actions()->update(
            'delete',
            fn (RowAction $rowAction) => $rowAction->setConfirmMessage(t('Do you really want to remove this holiday / internal day?')),
        );
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(ClosedDayFormType::class, [
            'closed_day' => $entity,
        ]);
    }

    #[Route(path: '/closed-day/holidays-import', name: 'admin_crud_closed_day_holidays_import')]
    #[CanEdit]
    public function holidaysImportAction(Request $request): Response
    {
        $holidaysImportData = $this->holidaysImportDataFactory->create();
        $form = $this->createForm(HolidaysImportFormType::class, $holidaysImportData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $importedCount = $this->holidaysImportFacade->import($form->getData());
                $this->addSuccessFlashTwig(t('{1} Imported <strong>%count%</strong> holiday.|[2,Inf] Imported <strong>%count%</strong> holidays.', ['%count%' => $importedCount]));
            } catch (InvalidCountry) {
                $this->addErrorFlash(t('The selected country is not valid.'));
            }

            return $this->redirectToRoute('admin_crud_closed_day_list');
        }

        return $this->render('@ShopsysAdministration/content/closedDay/holidaysImport.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
