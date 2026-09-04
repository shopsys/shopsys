<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DomainFilterController extends AdminBaseController
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
    ) {
    }

    /**
     * @param int[]|null $domainIds
     */
    #[RequireRole(SystemRole::ADMIN)]
    public function domainFilterTabsAction(string $namespace, ?array $domainIds = null): Response
    {
        return $this->render(
            '@ShopsysAdministration/partial/quick_domain_filter.html.twig',
            $this->getQuickDomainFilterParameters($namespace, $domainIds),
        );
    }

    /**
     * @param int[]|null $domainIds
     */
    #[RequireRole(SystemRole::ADMIN)]
    public function domainFilterSelectAction(string $namespace, ?array $domainIds = null): Response
    {
        return $this->render(
            '@ShopsysAdministration/partial/quick_domain_filter_select.html.twig',
            $this->getQuickDomainFilterParameters($namespace, $domainIds),
        );
    }

    /**
     * @param int[]|null $domainIds
     * @return array<string, mixed>
     */
    protected function getQuickDomainFilterParameters(string $namespace, ?array $domainIds): array
    {
        return [
            'domainConfigs' => $domainIds === null
                ? $this->domain->getAdminEnabledDomains()
                : array_filter(
                    $this->domain->getAdminEnabledDomains(),
                    static fn ($domainConfig): bool => in_array($domainConfig->getId(), $domainIds, true),
                ),
            'namespace' => $namespace,
            'selectedDomainId' => $this->adminDomainFilterTabsFacade->getSelectedDomainId($namespace, $domainIds),
        ];
    }

    #[Route(path: '/multidomain/filter-domain/{namespace}/{domainId}', requirements: ['domainId' => '\d+'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function selectDomainAction(Request $request, string $namespace, ?int $domainId = null): RedirectResponse
    {
        $this->adminDomainFilterTabsFacade->setSelectedDomainId($namespace, $domainId);

        $referer = $request->server->get('HTTP_REFERER');

        if ($referer === null) {
            return $this->redirectToRoute('admin_default_dashboard');
        }

        return $this->redirect($referer);
    }
}
