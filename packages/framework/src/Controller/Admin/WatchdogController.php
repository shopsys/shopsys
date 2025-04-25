<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Watchdog\Exception\WatchdogNotFoundException;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFacade;
use Shopsys\FrameworkBundle\Model\Watchdog\WatchdogGridFactory;
use Shopsys\FrameworkBundle\Twig\ProductExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WatchdogController extends AdminBaseController
{
    protected const string WATCHDOG_DOMAIN_FILTER_NAMESPACE = 'watchdogs';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogFacade $watchdogFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\Watchdog\WatchdogGridFactory $watchdogGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Twig\ProductExtension $productExtension
     */
    public function __construct(
        protected readonly WatchdogFacade $watchdogFacade,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly Domain $domain,
        protected readonly Localization $localization,
        protected readonly WatchdogGridFactory $watchdogGridFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly ProductExtension $productExtension,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/watchdog/list/')]
    public function listAction(Request $request): Response
    {
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->watchdogFacade->getWatchdogProductListQueryBuilderByQuickSearchData(
            $quickSearchForm->getData(),
            $this->localization->getCurrentLocaleForTranslatableEntities(),
        );

        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId(static::WATCHDOG_DOMAIN_FILTER_NAMESPACE);

        if ($selectedDomainId !== null) {
            $queryBuilder
                ->andWhere('w.domainId = :selectedDomainId')
                ->setParameter('selectedDomainId', $selectedDomainId);
        } else {
            $queryBuilder
                ->andWhere('w.domainId IN (:domainIds)')
                ->setParameter('domainIds', $this->domain->getAdminEnabledDomainIds());
        }

        return $this->render('@ShopsysFramework/Admin/Content/Watchdog/list.html.twig', [
            'gridView' => $this->watchdogGridFactory->createView($queryBuilder, $this->getCurrentAdministrator()),
            'domainFilterNamespace' => static::WATCHDOG_DOMAIN_FILTER_NAMESPACE,
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/watchdog/detail/{id}', requirements: ['id' => '\d+'])]
    public function detailAction(Request $request, int $id): Response
    {
        $product = $this->productFacade->getById($id);

        $quickSearchForm = $this->createForm(QuickSearchFormType::class, new QuickSearchFormData());
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->watchdogFacade->getWatchdogsByProductQueryBuilderByQuickSearchData(
            $product,
            $quickSearchForm->getData(),
        );

        $this->breadcrumbOverrider->overrideLastItem(
            t('Watchdog - %name%', ['%name%' => $this->productExtension->getProductDisplayName($product)]),
        );

        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId(static::WATCHDOG_DOMAIN_FILTER_NAMESPACE);

        if ($selectedDomainId !== null) {
            $queryBuilder
                ->andWhere('w.domainId = :selectedDomainId')
                ->setParameter('selectedDomainId', $selectedDomainId);
        } else {
            $queryBuilder
                ->andWhere('w.domainId IN (:domainIds)')
                ->setParameter('domainIds', $this->domain->getAdminEnabledDomainIds());
        }

        return $this->render('@ShopsysFramework/Admin/Content/Watchdog/detail.html.twig', [
            'gridView' => $this->watchdogGridFactory->createDetailView($queryBuilder, $this->getCurrentAdministrator()),
            'product' => $product,
            'domainFilterNamespace' => static::WATCHDOG_DOMAIN_FILTER_NAMESPACE,
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/watchdog/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): Response
    {
        try {
            $watchdog = $this->watchdogFacade->getById($id);

            $this->watchdogFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Watchdog for email <strong>{{ email }}</strong> has been removed'),
                [
                    'email' => $watchdog->getEmail(),
                ],
            );
        } catch (WatchdogNotFoundException $ex) {
            $this->addErrorFlash(t('Selected watchdog does not exist.'));

            return $this->redirectToRoute('admin_watchdog_list');
        }

        return $this->redirectToRoute('admin_watchdog_detail', ['id' => $watchdog->getProduct()->getId()]);
    }
}
