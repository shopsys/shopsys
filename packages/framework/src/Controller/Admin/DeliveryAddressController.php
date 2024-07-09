<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class DeliveryAddressController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
     */
    public function __construct(
        protected readonly DeliveryAddressFacade $deliveryAddressFacade,
    ) {
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    #[Route(path: '/delivery-address/delete/{id}', name: 'admin_delivery_address_delete', requirements: ['id' => '\d+'])]
    public function deleteAction(int $id): RedirectResponse
    {
        try {
            $city = $this->deliveryAddressFacade->getById($id)->getCity();

            $this->deliveryAddressFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Delivery address <strong>{{ city }}</strong> deleted'),
                [
                    'city' => $city,
                ],
            );
        } catch (DeliveryAddressNotFoundException $ex) {
            $this->addErrorFlash(t('Selected delivery address doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_customer_list');
    }
}
