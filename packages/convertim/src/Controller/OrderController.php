<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Controller;

use Convertim\Order\ConvertimOrderDataFactory;
use Convertim\Order\ConvertimSaveOrderResponse;
use Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider;
use Shopsys\ConvertimBundle\Model\Convertim\ConvertimLogger;
use Shopsys\ConvertimBundle\Model\Convertim\Exception\ConvertimException;
use Shopsys\ConvertimBundle\Model\Order\OrderDetailFactory;
use Shopsys\ConvertimBundle\Model\Order\OrderFacade;
use Shopsys\ConvertimBundle\Model\Order\OrderValidator;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

class OrderController extends AbstractConvertimController
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Order\OrderDetailFactory $orderDetailFactory
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimLogger $convertimLogger
     * @param \Shopsys\ConvertimBundle\Model\Order\OrderValidator $orderValidator
     * @param \Shopsys\ConvertimBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider $convertimConfigProvider
     */
    public function __construct(
        protected readonly OrderDetailFactory $orderDetailFactory,
        protected readonly ConvertimLogger $convertimLogger,
        protected readonly OrderValidator $orderValidator,
        protected readonly OrderFacade $orderFacade,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly OrderUrlGenerator $orderUrlGenerator,
        ConvertimConfigProvider $convertimConfigProvider,
    ) {
        parent::__construct($convertimConfigProvider);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/get-last-order-details/{email}')]
    public function getLastOrderDetail(Request $request): Response
    {
        if ($this->isProtectedRequest($request) === false) {
            return $this->invalidAuthorizationResponse();
        }

        try {
            return new JsonResponse($this->orderDetailFactory->createLastOrderDetail($request->attributes->get('email')));
        } catch (ConvertimException $e) {
            return $this->convertimLogger->logConvertimException($e);
        } catch (Throwable $e) {
            return $this->convertimLogger->logGenericException($e);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/check-order')]
    public function checkOrder(Request $request): Response
    {
        if ($this->isProtectedRequest($request) === false) {
            return $this->invalidAuthorizationResponse();
        }

        try {
            $rawData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $convertimOrderDataFactory = new ConvertimOrderDataFactory();
            $convertimOrderData = $convertimOrderDataFactory->createConvertimOrderDataFromJsonArray($rawData);
            $validationResult = $this->orderValidator->validateOrder($convertimOrderData);

            return new JsonResponse($validationResult);
        } catch (Throwable $e) {
            return $this->convertimLogger->logGenericException($e);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/save-order')]
    public function saveOrder(Request $request): Response
    {
        if ($this->isProtectedRequest($request) === false) {
            return $this->invalidAuthorizationResponse();
        }

        try {
            $rawData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $convertimOrderDataFactory = new ConvertimOrderDataFactory();
            $convertimOrderData = $convertimOrderDataFactory->createConvertimOrderDataFromJsonArray($rawData);

            $order = $this->orderFacade->saveOrder($convertimOrderData);

            $router = $this->domainRouterFactory->getRouter($order->getDomainId());

            $saveOrderResponse = new ConvertimSaveOrderResponse(
                $order->getUuid(),
                $order->getNumber(),
                $this->orderUrlGenerator->getOrderDetailUrl($order),
                $router->generate(
                    'front_order_paid',
                    [
                        'orderIdentifier' => $order->getUuid(),
                        'orderEmail' => $order->getEmail(),
                        'orderPaymentType' => $order->getPayment()->getType(),
                        'orderUrlHash' => $order->getUrlHash(),
                        'orderPaymentStatusPageValidityHash' => $order->getOrderPaymentStatusPageValidityHash(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                $router->generate(
                    'front_order_paid',
                    [
                        'orderIdentifier' => $order->getUuid(),
                        'orderEmail' => $order->getEmail(),
                        'orderPaymentType' => $order->getPayment()->getType(),
                        'orderUrlHash' => $order->getUrlHash(),
                        'orderPaymentStatusPageValidityHash' => $order->getOrderPaymentStatusPageValidityHash(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                $router->generate(
                    'front_order_sent',
                    [
                        'orderUuid' => $order->getUuid(),
                        'orderPaymentType' => $order->getPayment()->getType(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                [],
            );

            return new JsonResponse($saveOrderResponse);
        } catch (ConvertimException $e) {
            return $this->convertimLogger->logConvertimException($e);
        } catch (Throwable $e) {
            return $this->convertimLogger->logGenericException($e);
        }
    }
}
