<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Token;

use GraphQL\Error\FormattedError;
use Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator as BaseTokenAuthenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method __construct(\App\FrontendApi\Model\Token\TokenFacade $tokenFacade, \Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider $frontendApiUserProvider)
 */
class TokenAuthenticator extends BaseTokenAuthenticator
{
    protected const HEADER_AUTHORIZATION = 'X-Auth-Token';

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $formattedError = FormattedError::createFromException($exception);
        $formattedError['extensions']['userCode'] = 'invalid-token';

        // return errors as array until https://github.com/shopsys/shopsys/pull/2387 is resolved
        $responseData = [
            'errors' => [$formattedError],
        ];

        return new JsonResponse($responseData, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string|null
     */
    public function getCredentials(Request $request): ?string
    {
        $authorizationHeader = $request->headers->get(static::HEADER_AUTHORIZATION);
        if ($authorizationHeader === null) {
            return null;
        }

        return substr($authorizationHeader, strlen(static::BEARER));
    }
}
