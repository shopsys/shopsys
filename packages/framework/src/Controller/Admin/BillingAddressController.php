<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Customer\BillingAddressAndRelatedInfoFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_CUSTOMER)]
class BillingAddressController extends AdminBaseController
{
    public function __construct(
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BillingAddressDataFactory $billingAddressDataFactory,
        protected readonly BillingAddressFacade $billingAddressFacade,
    ) {
    }

    #[Route(path: '/billing-address/edit/{id}', name: 'admin_billing_address_edit', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
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

        return $this->render('@ShopsysAdministration/content/customer/billingAddress/edit.html.twig', [
            'form' => $form->createView(),
            'billingAddress' => $billingAddress,
        ]);
    }
}
