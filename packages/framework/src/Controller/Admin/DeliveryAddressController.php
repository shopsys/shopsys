<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Customer\DeliveryAddressFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_CUSTOMER)]
class DeliveryAddressController extends AdminBaseController
{
    public function __construct(
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
        protected readonly DeliveryAddressDataFactory $deliveryAddressDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly CustomerFacade $customerFacade,
    ) {
    }

    #[Route(path: '/delivery-address/new/{customerId}', name: 'admin_delivery_address_new', requirements: ['customerId' => '\d+'])]
    #[CanCreate]
    public function newAction(Request $request, int $customerId): Response
    {
        $customer = $this->customerFacade->getById($customerId);
        $deliveryAddressData = $this->deliveryAddressDataFactory->createForCustomer($customer);
        $deliveryAddressData->addressFilled = true;

        $form = $this->createForm(DeliveryAddressFormType::class, $deliveryAddressData, [
            'domain_id' => $customer->getDomainId(),
            'back_url' => $this->resolveBackUrl($customer),
            'back_url_text' => $this->resolveBackUrlText($customer),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $deliveryAddress = $this->deliveryAddressFacade->createIfAddressFilled($deliveryAddressData);

            $this->addSuccessFlashTwig(
                t('Delivery address <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $deliveryAddress->getFullAddress(),
                    'url' => $this->generateUrl('admin_delivery_address_edit', ['id' => $deliveryAddress->getId()]),
                ],
            );

            if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
                $billingAddress = $customer->getBillingAddress();

                return $this->redirectToRoute('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
            }

            $customerUsers = $this->customerFacade->getCustomerUsers($customer);
            $firstCustomerUser = array_first($customerUsers);

            return $this->redirectToRoute('admin_customer_edit', ['id' => $firstCustomerUser->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/customer/deliveryAddress/new.html.twig', [
            'form' => $form->createView(),
            'customer' => $customer,
        ]);
    }

    #[Route(path: '/delivery-address/edit/{id}', name: 'admin_delivery_address_edit', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $deliveryAddress = $this->deliveryAddressFacade->getById($id);
        $deliveryAddressData = $this->deliveryAddressDataFactory->createFromDeliveryAddress($deliveryAddress);

        $form = $this->createForm(DeliveryAddressFormType::class, $deliveryAddressData, [
            'domain_id' => $deliveryAddress->getCustomer()->getDomainId(),
            'back_url' => $this->resolveBackUrl($deliveryAddress->getCustomer()),
            'back_url_text' => $this->resolveBackUrlText($deliveryAddress->getCustomer()),
            'delivery_address' => $deliveryAddress,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->deliveryAddressFacade->edit($id, $deliveryAddressData);

            $this->addSuccessFlashTwig(
                t('Delivery address <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                [
                    'name' => $deliveryAddress->getFullAddress(),
                    'url' => $this->generateUrl('admin_delivery_address_edit', ['id' => $deliveryAddress->getId()]),
                ],
            );

            $customer = $deliveryAddress->getCustomer();

            if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
                $billingAddress = $customer->getBillingAddress();

                return $this->redirectToRoute('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
            }

            $customerUsers = $this->customerFacade->getCustomerUsers($customer);
            $firstCustomerUser = array_first($customerUsers);

            return $this->redirectToRoute('admin_customer_edit', ['id' => $firstCustomerUser->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing delivery address - %name%', ['%name%' => $deliveryAddress->getFullAddress()]),
        );

        return $this->render('@ShopsysAdministration/content/customer/deliveryAddress/edit.html.twig', [
            'form' => $form->createView(),
            'deliveryAddress' => $deliveryAddress,
        ]);
    }

    #[Route(path: '/delivery-address/delete/{id}', name: 'admin_delivery_address_delete', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): RedirectResponse
    {
        $deliveryAddress = $this->deliveryAddressFacade->getById($id);
        $customer = $deliveryAddress->getCustomer();

        try {
            $deliveryAddressFullAddress = $deliveryAddress->getFullAddress();

            $this->deliveryAddressFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Delivery address <strong>{{ deliveryAddressFullAddress }}</strong> deleted'),
                [
                    'deliveryAddressFullAddress' => $deliveryAddressFullAddress,
                ],
            );
        } catch (DeliveryAddressNotFoundException $ex) {
            $this->addErrorFlash(t('Selected delivery address doesn\'t exist.'));
        }

        if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
            $billingAddress = $customer->getBillingAddress();

            return $this->redirectToRoute('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
        }

        return $this->redirectToRoute('admin_customer_list');
    }

    protected function resolveBackUrl(Customer $customer): string
    {
        if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
            $billingAddress = $customer->getBillingAddress();

            return $this->generateUrl('admin_billing_address_edit', ['id' => $billingAddress->getId()]);
        }

        $customerUsers = $this->customerFacade->getCustomerUsers($customer);
        $firstCustomerUser = array_first($customerUsers);

        return $this->generateUrl('admin_customer_edit', ['id' => $firstCustomerUser->getId()]);
    }

    protected function resolveBackUrlText(Customer $customer): string
    {
        if ($this->customerFacade->isB2bFeaturesEnabledByCustomer($customer)) {
            $billingAddress = $customer->getBillingAddress();

            return t('Back to customer {{ name }}', ['{{ name }}' => $billingAddress->getCompanyName()]);
        }

        $customerUsers = $this->customerFacade->getCustomerUsers($customer);
        $firstCustomerUser = array_first($customerUsers);

        return t('Back to customer {{ name }}', ['{{ name }}' => $firstCustomerUser->getCustomerUserFullName()]);
    }
}
