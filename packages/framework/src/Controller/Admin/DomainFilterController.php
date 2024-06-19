<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DomainFilterController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
    ) {
    }

    /**
     * @param string $namespace
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function domainFilterTabsAction(string $namespace): Response
    {
        return $this->render('@ShopsysFramework/Admin/Inline/Domain/filter.html.twig', [
            'domainConfigs' => $this->domain->getAll(),
            'namespace' => $namespace,
            'selectedDomainId' => $this->adminDomainFilterTabsFacade->getSelectedDomainId($namespace),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $namespace
     * @param int|null $domainId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route(path: '/multidomain/filter-domain/{namespace}/{domainId}', requirements: ['domainId' => '\d+'])]
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
