<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
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

#[ForRole(AdminRoleConstant::ROLE_WATCHDOG)]
class WatchdogController extends AdminBaseController
{
    protected const string WATCHDOG_DOMAIN_FILTER_NAMESPACE = 'watchdogs';

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

    #[Route(path: '/watchdog/list/')]
    #[CanView]
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

        return $this->render('@ShopsysAdministration/content/watchdog/list.html.twig', [
            'gridView' => $this->watchdogGridFactory->createView($queryBuilder, $this->getCurrentAdministrator()),
            'domainFilterNamespace' => static::WATCHDOG_DOMAIN_FILTER_NAMESPACE,
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    #[Route(path: '/watchdog/detail/{id}', requirements: ['id' => '\d+'])]
    #[CanView]
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

        return $this->render('@ShopsysAdministration/content/watchdog/detail.html.twig', [
            'gridView' => $this->watchdogGridFactory->createDetailView($queryBuilder, $this->getCurrentAdministrator()),
            'product' => $product,
            'domainFilterNamespace' => static::WATCHDOG_DOMAIN_FILTER_NAMESPACE,
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }

    #[Route(path: '/watchdog/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
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
