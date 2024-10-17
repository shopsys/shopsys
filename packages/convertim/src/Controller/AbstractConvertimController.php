<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Controller;

use Convertim\ConvertimBackendInterface;
use Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractConvertimController
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider $convertimConfigProvider
     */
    public function __construct(
        protected readonly ConvertimConfigProvider $convertimConfigProvider,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    protected function isProtectedRequest(Request $request): bool
    {
        $authorizationHeader = $request->headers->get(ConvertimBackendInterface::CONVERTIM_AUTHORIZATION_HEADER);

        return $authorizationHeader === $this->convertimConfigProvider->getConfigForCurrentDomain()->getAuthorizationHeader();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function invalidAuthorizationResponse(): Response
    {
        return new JsonResponse(['error' => 'Token is invalid'], Response::HTTP_UNAUTHORIZED);
    }
}
