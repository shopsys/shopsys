<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\PriceList\PriceListFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PriceListController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListGridFactory $priceListGridFactory
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade $priceListFacade
     * @param \Shopsys\FrameworkBundle\Model\PriceList\PriceListDataFactory $priceListDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly PriceListGridFactory $priceListGridFactory,
        protected readonly PriceListFacade $priceListFacade,
        protected readonly PriceListDataFactory $priceListDataFactory,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly Domain $domain,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/pricing/price-list/list/')]
    public function listAction(): Response
    {
        $domainFilterNamespace = 'priceList';

        $queryBuilder = $this->priceListFacade->getPriceListGridQueryBuilder();

        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

        if ($selectedDomainId !== null) {
            $queryBuilder
                ->andWhere('pl.domainId = :selectedDomainId')
                ->setParameter('selectedDomainId', $selectedDomainId);
        } else {
            $queryBuilder
                ->andWhere('pl.domainId IN (:domainIds)')
                ->setParameter('domainIds', $this->domain->getAdminEnabledDomainIds());
        }

        return $this->render('@ShopsysFramework/Admin/Content/PriceList/list.html.twig', [
            'gridView' => $this->priceListGridFactory->createView($queryBuilder, $this->getCurrentAdministrator()),
            'domainFilterNamespace' => $domainFilterNamespace,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/pricing/price-list/new/')]
    public function newAction(Request $request): Response
    {
        $priceListData = $this->priceListDataFactory->create();

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

        return $this->render('@ShopsysFramework/Admin/Content/PriceList/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/pricing/price-list/edit/{id}', requirements: ['id' => '\d+'])]
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

        return $this->render('@ShopsysFramework/Admin/Content/PriceList/edit.html.twig', [
            'form' => $form->createView(),
            'priceList' => $priceList,
        ]);
    }
}
