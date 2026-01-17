<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\Admin\Administrator\AdminDomainsFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactory;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SelectAdminDomainsController extends AdminBaseController
{
    public function __construct(
        protected readonly AdministratorDataFactory $administratorDataFactory,
        protected readonly AdministratorFacade $administratorFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[RequireRole(SystemRole::ADMIN)]
    public function renderFormAction(): Response
    {
        $form = $this->createForm(AdminDomainsFormType::class, $this->domain->getAdminEnabledDomainIds());

        return $this->render('@ShopsysAdministration/partial/select_admin_domains.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/domains/filter', name: 'admin_domains_filter', methods: ['POST'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function filterDomainsAction(Request $request): Response
    {
        $form = $this->createForm(AdminDomainsFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (count($data) === 0) {
                $this->addErrorFlash(t('Please select at least one domain.'));
            } else {
                if (count($data) === count($this->domain->getAllIds())) {
                    $data = [];
                }

                $administrator = $this->getCurrentAdministrator();
                $administratorData = $this->administratorDataFactory->createFromAdministrator($administrator);
                $administratorData->displayOnlyDomainIds = $data;
                $this->administratorFacade->edit($administrator->getId(), $administratorData);
            }

            $referer = $request->headers->get('referer');

            return $referer !== null ? $this->redirect($referer) : $this->redirectToRoute('admin_default_dashboard');
        }

        return $this->redirectToRoute('admin_default_dashboard');
    }
}
