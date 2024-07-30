<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\Customer\BillingAddressAndRelatedInfoFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BillingAddressController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade $billingAddressFacade
     */
    public function __construct(
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BillingAddressDataFactory $billingAddressDataFactory,
        protected readonly BillingAddressFacade $billingAddressFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/billing-address/edit/{id}', name: 'admin_billing_address_edit', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $billingAddress = $this->billingAddressFacade->getById($id);
        $billingAddressData = $this->billingAddressDataFactory->createFromBillingAddress($billingAddress);

        $form = $this->createForm(BillingAddressAndRelatedInfoFormType::class, $billingAddressData, [
            'customer' => $billingAddress->getCustomer(),
            'domain_id' => $this->adminDomainTabsFacade->getSelectedDomainId(),
            'disableCompanyCustomerCheckbox' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->billingAddressFacade->edit($billingAddress->getId(), $billingAddressData);
            $this->addSuccessFlashTwig(
                t('Billing address <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $billingAddress->getCompanyName(),
                    'url' => $this->generateUrl('admin_billing_address_edit', ['id' => $billingAddress->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_customer_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing billing address - %name%', ['%name%' => $billingAddress->getCompanyName()]),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Customer/BillingAddress/edit.html.twig', [
            'form' => $form->createView(),
            'billingAddress' => $billingAddress,
        ]);
    }
}
