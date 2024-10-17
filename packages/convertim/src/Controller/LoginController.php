<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Controller;

use Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider;
use Shopsys\ConvertimBundle\Model\Convertim\ConvertimLogger;
use Shopsys\ConvertimBundle\Model\Convertim\Exception\ConvertimException;
use Shopsys\ConvertimBundle\Model\Login\LoginDetailFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class LoginController extends AbstractConvertimController
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Login\LoginDetailFactory $loginDetailsFactory
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimLogger $convertimLogger
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider $convertimConfigProvider
     */
    public function __construct(
        protected readonly LoginDetailFactory $loginDetailsFactory,
        protected readonly ConvertimLogger $convertimLogger,
        ConvertimConfigProvider $convertimConfigProvider,
    ) {
        parent::__construct($convertimConfigProvider);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/get-login-details/{email}')]
    public function getLoginDetail(Request $request): Response
    {
        if ($this->isProtectedRequest($request) === false) {
            return $this->invalidAuthorizationResponse();
        }

        try {
            return new JsonResponse($this->loginDetailsFactory->createLoginDetail($request->attributes->get('email')));
        } catch (ConvertimException $e) {
            return $this->convertimLogger->logConvertimException($e);
        } catch (Throwable $e) {
            return $this->convertimLogger->logGenericException($e);
        }
    }
}
