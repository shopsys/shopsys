<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentStatusNotifyController extends AbstractController
{
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly PaymentServiceFacade $paymentServiceFacade,
    ) {
    }

    public function notifyAction(Request $request): Response
    {
        $orderIdentifier = trim((string)$request->query->get('orderIdentifier'));

        if ($orderIdentifier === '') {
            throw new NotFoundRedirectToStorefrontException();
        }

        try {
            $order = $this->orderFacade->getByUuid($orderIdentifier);
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundRedirectToStorefrontException($exception->getMessage(), $exception);
        }

        if ($this->paymentServiceFacade->updatePaymentTransactionsByOrder($order)) {
            $this->orderFacade->updatePaymentByLastPaymentTransaction($order);
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
