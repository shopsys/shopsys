<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Override;
use Psr\Clock\ClockInterface;
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
use Shopsys\AdministrationBundle\Model\PriceList\PriceListCrudHandler;
use Shopsys\FrameworkBundle\Component\HttpFoundation\CsvResponse;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Form\Admin\PriceList\ImportPriceListFormType;
use Shopsys\FrameworkBundle\Form\Admin\PriceList\PriceListFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\SideMenuBuilder;
use Shopsys\FrameworkBundle\Model\PriceList\Exception\PriceListNotFoundException;
use Shopsys\FrameworkBundle\Model\PriceList\PriceList;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListCsvColumnsEnum;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[CrudController(PriceList::class)]
#[ForRole(AdminRoleConstant::ROLE_PRICE_LIST)]
class PriceListController extends AbstractCrudController
{
    public function __construct(
        protected readonly PriceListFacade $priceListFacade,
        protected readonly PriceListDataFactory $priceListDataFactory,
        protected readonly PriceListCsvColumnsEnum $priceListCsvColumnsEnum,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly TransformStringHelper $transformStringHelper,
        protected readonly ClockInterface $clock,
    ) {
    }

    #[Override]
    public function configure(CrudConfig $config): void
    {
        $config
            ->setRoutePrefix('/pricing')
            ->setMenuSection(SideMenuBuilder::ROOT_PRICING, null, 'first')
            ->setListDomainControl(CrudListDomainControl::QUICK_FILTER)
            ->registerHandler(PriceListCrudHandler::class);
    }

    #[Override]
    protected function configureActions(ActionsConfig $actions): void
    {
        $actions->add(
            ActionType::LIST,
            Action::create('import', t('Import CSV'))
                ->setIcon('upload')
                ->linkToRoute('admin_crud_price_list_import'),
        );

        $actions->add(
            ActionType::EDIT,
            Action::create('export', t('Export CSV'))
                ->setIcon('download')
                ->linkToRoute('admin_crud_price_list_export', fn (PriceList $priceList): array => ['id' => $priceList->getId()]),
        );
    }

    #[Override]
    protected function configureQuery(QueryBuilder $queryBuilder): void
    {
        $queryBuilder
            ->addSelect('CASE
                    WHEN :now BETWEEN o.validFrom AND o.validTo THEN 0
                    WHEN :now < o.validFrom THEN 1
                    ELSE -1
                END AS validityStatus')
            ->setParameter('now', $this->clock->now());
    }

    #[Override]
    protected function configureDatagrid(Datagrid $datagrid): void
    {
        $datagrid
            ->add('name', [
                'label' => t('Price list name'),
            ])
            ->add('validFrom', [
                'label' => t('Valid from'),
            ])
            ->add('validTo', [
                'label' => t('Valid to'),
            ]);

        if ($this->domain->isMultidomain()) {
            $datagrid->add('domainId', [
                'label' => t('Domain'),
            ]);
        }

        $datagrid
            ->add('validityStatus', [
                'label' => t('Status'),
                'virtual' => true,
                'property' => 'validityStatus',
                'template' => '@ShopsysAdministration/content/priceList/grid/validityStatus.html.twig',
            ])
            ->add('lastUpdate', [
                'label' => t('Last update'),
            ]);

        $datagrid->setDefaultOrder('lastUpdate', OrderingEnum::DESC);

        $datagrid->actions()->update(
            'delete',
            fn (RowAction $rowAction) => $rowAction->setConfirmMessage(
                t('Do you really want to remove this product list? Special prices for products in this list will be removed.'),
            ),
        );

        $datagrid->actions()->add(
            RowAction::create('export', t('Export CSV'), 'download')
                ->linkToRoute('admin_crud_price_list_export', fn (array $row): array => ['id' => $row['id']]),
        );
    }

    #[Override]
    protected function configureForm(CrudFormConfigurator $formConfigurator, ?object $entity = null): void
    {
        $formConfigurator->useFormType(PriceListFormType::class, [
            'priceList' => $entity,
        ]);
    }

    #[Route(path: '/pricing/price-list/export/{id}', name: 'admin_crud_price_list_export', requirements: ['id' => '\d+'])]
    #[CanView]
    public function exportAction(int $id): Response
    {
        try {
            $priceList = $this->priceListFacade->getById($id);
            $sanitizedPriceListName = $this->transformStringHelper->safeFilename($priceList->getName());
            $priceListDataToExport = $this->priceListFacade->getPriceListDataToExport($id);

            return new CsvResponse(
                $priceListDataToExport,
                'price_list_' . $id . '_' . $sanitizedPriceListName . '.csv',
                $this->priceListCsvColumnsEnum->getAllCases(),
            );
        } catch (PriceListNotFoundException) {
            $this->addErrorFlash(t('Selected price list does not exist.'));

            return $this->redirectToRoute('admin_crud_price_list_list');
        }
    }

    #[Route(path: '/pricing/price-list/import', name: 'admin_crud_price_list_import')]
    #[CanCreate]
    public function importAction(Request $request): Response
    {
        $form = $this->createForm(ImportPriceListFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['selectPriceList'] === null) {
                $priceListData = $this->priceListDataFactory->create();
                $priceListData->domainId = $data['domainId'];
            } else {
                $priceListData = $this->priceListDataFactory->createFromPriceList($data['selectPriceList']);
            }

            $priceListData->name = $data['name'];
            $priceListData->validFrom = $data['validFrom'];
            $priceListData->validTo = $data['validTo'];

            $importResult = $this->priceListFacade->importPriceList(
                $priceListData,
                $data['csvFile'],
            );

            if ($importResult->hasErrors()) {
                $this->addErrorFlash(t('Error while importing CSV file.'));

                foreach ($importResult->getErrors() as $error) {
                    $this->addErrorFlash($error);
                }

                return $this->render('@ShopsysAdministration/content/priceList/import.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $this->addSuccessFlash(t(
                '%count% item from CSV file was imported successfully.|%count% items from CSV file were imported successfully.',
                ['%count%' => $importResult->getImportedCount()],
            ));

            if ($data['selectPriceList'] === null) {
                $this->addSuccessFlash(t(
                    'New price list <strong><a href="{{ url }}">{{ name }}</a></strong> was created.',
                    [
                        '{{ name }}' => $importResult->getPriceListName(),
                        '{{ url }}' => $this->generateUrl('admin_crud_price_list_edit', ['id' => $importResult->getPriceListId()]),
                    ],
                ));
            } else {
                $this->addSuccessFlash(t(
                    'Price list <strong><a href="{{ url }}">{{ name }}</a></strong> was replaced.',
                    [
                        '{{ name }}' => $importResult->getPriceListName(),
                        '{{ url }}' => $this->generateUrl('admin_crud_price_list_edit', ['id' => $importResult->getPriceListId()]),
                    ],
                ));
            }

            if ($importResult->hasWarnings()) {
                $this->addWarningFlash(t('Some items cannot be imported due to errors:'));

                foreach ($importResult->getWarnings() as $warning) {
                    $this->addWarningFlash($warning);
                }
            }

            return $this->redirectToRoute('admin_crud_price_list_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Error while importing CSV file.'));
        }

        return $this->render('@ShopsysAdministration/content/priceList/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/pricing/price-list/load-metadata/{id}', name: 'admin_crud_price_list_load_metadata', requirements: ['id' => '\d+'], condition: 'request.isXmlHttpRequest()')]
    #[CanView]
    public function loadMetadataAction(int $id): Response
    {
        try {
            $priceList = $this->priceListFacade->getById($id);
            $validFrom = $priceList->getValidFrom()->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin());
            $validTo = $priceList->getValidTo()->setTimezone($this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin());

            return new JsonResponse([
                'result' => 'valid',
                'name' => $priceList->getName(),
                'validFrom' => $validFrom->format('d.m.Y H:i:s'),
                'validTo' => $validTo->format('d.m.Y H:i:s'),
            ]);
        } catch (PriceListNotFoundException) {
            return new JsonResponse([
                'result' => 'invalid',
                'errors' => 'Price list not found',
            ]);
        }
    }
}
