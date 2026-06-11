<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\InvalidCredentialsUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\TooManyLoginAttemptsUserError;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultData;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\Exception\SocialNetworkLoginException;
use Shopsys\FrontendApiBundle\Model\SocialNetwork\SocialNetworkFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\RateLimiter\DefaultLoginRateLimiter;

class LoginWithCredentialMutation extends AbstractMutation
{
    public function __construct(
        protected readonly SocialNetworkFacade $socialNetworkFacade,
        protected readonly RequestStack $requestStack,
        protected readonly DefaultLoginRateLimiter $loginRateLimiter,
    ) {
    }

    public function loginWithCredentialMutation(Argument $argument): LoginResultData
    {
        $input = $argument['input'];

        $currentRequest = $this->checkLoginRateLimitAndGetCurrentRequest();

        try {
            $loginResultData = $this->socialNetworkFacade->loginWithCredential(
                $input['type'],
                $input['credential'],
                $input['cartUuid'] ?? null,
                $input['productListsUuids'] ?? [],
                $input['shouldOverwriteCustomerUserCart'] ?? false,
                $input['nonce'] ?? null,
            );
        } catch (SocialNetworkLoginException) {
            throw new InvalidCredentialsUserError('Log in via FedCM failed.');
        }

        $this->loginRateLimiter->reset($currentRequest);

        return $loginResultData;
    }

    protected function checkLoginRateLimitAndGetCurrentRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            throw new InvalidCredentialsUserError('Request is not available.');
        }

        $limit = $this->loginRateLimiter->consume($request);

        if (!$limit->isAccepted()) {
            throw new TooManyLoginAttemptsUserError('Too many login attempts. Try again later.');
        }

        return $request;
    }
}
