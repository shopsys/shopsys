<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Customer\DeliveryAddressFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeliveryAddressController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     */
    public function __construct(
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
        protected readonly DeliveryAddressDataFactory $deliveryAddressDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly CustomerFacade $customerFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $customerId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/delivery-address/new/{customerId}', name: 'admin_delivery_address_new', requirements: ['customerId' => '\d+'])]
    public function newAction(Request $request, int $customerId): Response
    {
        $customer = $this->customerFacade->getById($customerId);
        $deliveryAddressData = $this->deliveryAddressDataFactory->createForCustomer($customer);
        $deliveryAddressData->addressFilled = true;

        $form = $this->createForm(DeliveryAddressFormType::class, $deliveryAddressData, [
            'domain_id' => $customer->getDomainId(),
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
            $firstCustomerUser = reset($customerUsers);

            return $this->redirectToRoute('admin_customer_edit', ['id' => $firstCustomerUser->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Customer/DeliveryAddress/new.html.twig', [
            'form' => $form->createView(),
            'customer' => $customer,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/delivery-address/edit/{id}', name: 'admin_delivery_address_edit', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $deliveryAddress = $this->deliveryAddressFacade->getById($id);
        $deliveryAddressData = $this->deliveryAddressDataFactory->createFromDeliveryAddress($deliveryAddress);

        $form = $this->createForm(DeliveryAddressFormType::class, $deliveryAddressData, [
            'domain_id' => $deliveryAddress->getCustomer()->getDomainId(),
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
            $firstCustomerUser = reset($customerUsers);

            return $this->redirectToRoute('admin_customer_edit', ['id' => $firstCustomerUser->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing delivery address - %name%', ['%name%' => $deliveryAddress->getFullAddress()]),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Customer/DeliveryAddress/edit.html.twig', [
            'form' => $form->createView(),
            'deliveryAddress' => $deliveryAddress,
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route(path: '/delivery-address/delete/{id}', name: 'admin_delivery_address_delete', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): RedirectResponse
    {
        $deliveryAddress = $this->deliveryAddressFacade->getById($id);
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

        return $this->redirectToRoute('admin_customer_list');
    }
}
