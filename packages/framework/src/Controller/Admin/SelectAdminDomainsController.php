<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Administrator\AdminDomainsFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SelectAdminDomainsController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorDataFactoryInterface $administratorDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade $administratorFacade
     */
    public function __construct(
        protected readonly AdministratorDataFactoryInterface $administratorDataFactory,
        protected readonly AdministratorFacade $administratorFacade,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFormAction(): Response
    {
        $administrator = $this->getCurrentAdministrator();

        $form = $this->createForm(AdminDomainsFormType::class, $administrator->getDisplayOnlyDomainIds());

        return $this->renderForm('@ShopsysFramework/Admin/Form/adminDomainsForm.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/admin/domains/filter', name: 'admin_domains_filter', methods: ['POST'])]
    public function filterDomainsAction(Request $request): Response
    {
        $form = $this->createForm(AdminDomainsFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (count($data) === 0) {
                $this->addErrorFlash(t('Please select at least one domain.'));
            } else {
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
