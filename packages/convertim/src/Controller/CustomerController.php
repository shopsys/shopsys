<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Controller;

use Exception;
use Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider;
use Shopsys\ConvertimBundle\Model\Convertim\ConvertimLogger;
use Shopsys\ConvertimBundle\Model\Convertim\Exception\ConvertimException;
use Shopsys\ConvertimBundle\Model\Customer\CustomerDetailFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CustomerController extends AbstractConvertimController
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Customer\CustomerDetailFactory $customerDetailFactory
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimLogger $convertimLogger
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider $convertimConfigProvider
     */
    public function __construct(
        protected readonly CustomerDetailFactory $customerDetailFactory,
        protected readonly ConvertimLogger $convertimLogger,
        ConvertimConfigProvider $convertimConfigProvider,
    ) {
        parent::__construct($convertimConfigProvider);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/get-customer-details/{customerUuid}')]
    public function getCustomerDetail(Request $request): Response
    {
        if ($this->isProtectedRequest($request) === false) {
            return $this->invalidAuthorizationResponse();
        }

        try {
            return new JsonResponse($this->customerDetailFactory->createCustomerDetail($request->attributes->get('customerUuid')));
        } catch (ConvertimException $e) {
            return $this->convertimLogger->logConvertimException($e);
        } catch (Exception $e) {
            return $this->convertimLogger->logGenericException($e);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/get-customer-details-by-order/{orderUuid}')]
    public function getCustomerDetailByOrderUuid(Request $request): Response
    {
        if ($this->isProtectedRequest($request) === false) {
            return $this->invalidAuthorizationResponse();
        }

        try {
            return new JsonResponse($this->customerDetailFactory->createCustomerDetailByOrderUuid($request->attributes->get('orderUuid')));
        } catch (ConvertimException $e) {
            return $this->convertimLogger->logConvertimException($e);
        } catch (Exception $e) {
            return $this->convertimLogger->logGenericException($e);
        }
    }
}
