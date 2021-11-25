<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Token;

use GraphQL\Error\FormattedError;
use Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator as BaseTokenAuthenticatorAlias;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method __construct(\App\FrontendApi\Model\Token\TokenFacade $tokenFacade)
 */
class TokenAuthenticator extends BaseTokenAuthenticatorAlias
{
    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // return errors as array until https://github.com/shopsys/shopsys/pull/2387 is resolved
        $responseData = [
            'errors' => [FormattedError::createFromException($exception)],
        ];

        return new JsonResponse($responseData, Response::HTTP_UNAUTHORIZED);
    }
}
