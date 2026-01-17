<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\CsvResponse;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Form\Admin\PriceList\ImportPriceListFormType;
use Shopsys\FrameworkBundle\Form\Admin\PriceList\PriceListFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\PriceList\Exception\PriceListNotFoundException;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListCsvColumnsEnum;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListGridFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_PRICE_LIST)]
class PriceListController extends AdminBaseController
{
    protected const string DOMAIN_FILTER_NAMESPACE = 'priceList';

    public function __construct(
        protected readonly PriceListGridFactory $priceListGridFactory,
        protected readonly PriceListFacade $priceListFacade,
        protected readonly PriceListDataFactory $priceListDataFactory,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly Domain $domain,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly PriceListCsvColumnsEnum $priceListCsvColumnsEnum,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    #[Route(path: '/pricing/price-list/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $queryBuilder = $this->priceListFacade->getPriceListGridQueryBuilder();

        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId(static::DOMAIN_FILTER_NAMESPACE);

        if ($selectedDomainId !== null) {
            $queryBuilder
                ->andWhere('pl.domainId = :selectedDomainId')
                ->setParameter('selectedDomainId', $selectedDomainId);
        } else {
            $queryBuilder
                ->andWhere('pl.domainId IN (:domainIds)')
                ->setParameter('domainIds', $this->domain->getAdminEnabledDomainIds());
        }

        return $this->render('@ShopsysAdministration/content/priceList/list.html.twig', [
            'gridView' => $this->priceListGridFactory->createView($queryBuilder, $this->getCurrentAdministrator()),
            'domainFilterNamespace' => static::DOMAIN_FILTER_NAMESPACE,
        ]);
    }

    #[Route(path: '/pricing/price-list/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $priceListData = $this->priceListDataFactory->create();
        $priceListData->domainId = $request->get(
            'domainId',
            $this->adminDomainFilterTabsFacade->getSelectedDomainId(static::DOMAIN_FILTER_NAMESPACE) ?? Domain::FIRST_DOMAIN_ID,
        );

        $form = $this->createForm(PriceListFormType::class, $priceListData, [
            'priceList' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $priceList = $this->priceListFacade->create($priceListData);

            $this->addSuccessFlashTwig(
                t('Price list <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $priceList->getName(),
                    'url' => $this->generateUrl('admin_pricelist_edit', ['id' => $priceList->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_pricelist_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/priceList/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/pricing/price-list/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $priceList = $this->priceListFacade->getById($id);
        $priceListData = $this->priceListDataFactory->createFromPriceList($priceList);

        $form = $this->createForm(PriceListFormType::class, $priceListData, [
            'priceList' => $priceList,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->priceListFacade->edit($priceList->getId(), $priceListData);

            $this->addSuccessFlashTwig(
                t('Price list <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $priceList->getName(),
                    'url' => $this->generateUrl('admin_pricelist_edit', ['id' => $priceList->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_pricelist_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing price list - %name%', ['%name%' => $priceList->getName()]));

        return $this->render('@ShopsysAdministration/content/priceList/edit.html.twig', [
            'form' => $form->createView(),
            'priceList' => $priceList,
        ]);
    }

    #[Route(path: '/pricing/price-list/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $priceList = $this->priceListFacade->getById($id);

            $this->priceListFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Price list <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $priceList->getName(),
                ],
            );
        } catch (PriceListNotFoundException) {
            $this->addErrorFlash(t('Selected price list does not exist.'));
        }

        return $this->redirectToRoute('admin_pricelist_list');
    }

    #[Route(path: '/pricing/price-list/export/{id}', requirements: ['id' => '\d+'])]
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

            return $this->redirectToRoute('admin_pricelist_list');
        }
    }

    #[Route(path: '/pricing/price-list/import')]
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
                        '{{ url }}' => $this->generateUrl('admin_pricelist_edit', ['id' => $importResult->getPriceListId()]),
                    ],
                ));
            } else {
                $this->addSuccessFlash(t(
                    'Price list <strong><a href="{{ url }}">{{ name }}</a></strong> was replaced.',
                    [
                        '{{ name }}' => $importResult->getPriceListName(),
                        '{{ url }}' => $this->generateUrl('admin_pricelist_edit', ['id' => $importResult->getPriceListId()]),
                    ],
                ));
            }

            if ($importResult->hasWarnings()) {
                $this->addWarningFlash(t('Some items cannot be imported due to errors:'));

                foreach ($importResult->getWarnings() as $warning) {
                    $this->addWarningFlash($warning);
                }
            }

            return $this->redirectToRoute('admin_pricelist_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Error while importing CSV file.'));
        }

        return $this->render('@ShopsysAdministration/content/priceList/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/pricing/price-list/loadMetadata/{id}', requirements: ['id' => '\d+'], condition: 'request.isXmlHttpRequest()')]
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
