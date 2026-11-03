<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Front;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\HttpFoundation\Exception\NotFoundRedirectToStorefrontException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayPaymentDownloadException;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentStatusNotifyController extends AbstractController
{
    protected const string QUERY_PARAMETER_ORDER_IDENTIFIER = 'orderIdentifier';
    protected const string QUERY_PARAMETER_EXTERNAL_PAYMENT_IDENTIFIER = 'id';

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly PaymentServiceFacade $paymentServiceFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function notifyAction(Request $request): Response
    {
        $orderIdentifier = trim($request->query->getString(static::QUERY_PARAMETER_ORDER_IDENTIFIER));
        $externalPaymentIdentifier = trim($request->query->getString(static::QUERY_PARAMETER_EXTERNAL_PAYMENT_IDENTIFIER));

        if (!Uuid::isValid($orderIdentifier) || $externalPaymentIdentifier === '') {
            throw new NotFoundRedirectToStorefrontException();
        }

        try {
            $order = $this->orderFacade->getByUuid($orderIdentifier);
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundRedirectToStorefrontException($exception->getMessage(), $exception);
        }

        if (!$order->hasPaymentTransactionWithExternalPaymentIdentifier($externalPaymentIdentifier)) {
            throw new NotFoundRedirectToStorefrontException();
        }

        try {
            if ($this->paymentServiceFacade->updatePaymentTransactionsByOrder($order)) {
                $this->orderFacade->updatePaymentByLastPaymentTransaction($order);
            }
        } catch (GoPayPaymentDownloadException $exception) {
            $this->logger->error('Payment status notification could not be processed.', [
                ...$exception->getLogContext(),
                'orderId' => $order->getId(),
            ]);

            return new JsonResponse(['status' => 'error'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
