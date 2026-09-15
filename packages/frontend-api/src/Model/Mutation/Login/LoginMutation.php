<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\InvalidCredentialsUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\TooManyLoginAttemptsUserError;
use Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultData;
use Shopsys\FrontendApiBundle\Model\Security\TokensData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\RateLimiter\DefaultLoginRateLimiter;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginMutation extends AbstractMutation
{
    public function __construct(
        protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
        protected readonly UserPasswordHasherInterface $userPasswordHasher,
        protected readonly DefaultLoginRateLimiter $loginRateLimiter,
        protected readonly RequestStack $requestStack,
        protected readonly LoginAsUserFacade $loginAsUserFacade,
    ) {
    }

    public function loginMutation(Argument $argument): LoginResultData
    {
        $input = $argument['input'];

        $currentRequest = $this->checkLoginRateLimitAndGetCurrentRequest($input['email']);

        try {
            $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($input['email']);
        } catch (UserNotFoundException) {
            $this->loginRateLimiter->consume($currentRequest);

            throw new InvalidCredentialsUserError('Log in failed.');
        }

        if (!$this->userPasswordHasher->isPasswordValid($customerUser, $input['password'])) {
            $this->loginRateLimiter->consume($currentRequest);

            throw new InvalidCredentialsUserError('Log in failed.');
        }

        $this->loginRateLimiter->reset($currentRequest);

        return $this->loginAsUserFacade->runLoginSteps(
            $customerUser,
            LoginTypeEnum::WEB,
            false,
            $input['productListsUuids'] ?? [],
            $input['shouldOverwriteCustomerUserCart'] ?? false,
            $input['cartUuid'] ?? null,
            null,
        );
    }

    public function loginViaExchangeTokenMutation(Argument $argument): TokensData
    {
        $exchangeToken = $argument['exchangeToken'];

        $currentRequest = $this->checkLoginRateLimitAndGetCurrentRequest();

        try {
            $tokensData = $this->loginAsUserFacade->loginAdministratorAsCustomerUserAndGetAccessAndRefreshToken($exchangeToken);
            $this->loginRateLimiter->reset($currentRequest);

            return $tokensData;
        } catch (LoginAsRememberedUserException) {
            $this->loginRateLimiter->consume($currentRequest);

            throw new InvalidCredentialsUserError('Invalid or expired exchange token.');
        }
    }

    /**
     * Only failed attempts consume the limit (see the callers), a successful login must not,
     * because DefaultLoginRateLimiter::reset() clears just the username+IP limit
     * and the IP-wide limit would otherwise be exhausted by legitimate logins from a shared IP address
     */
    protected function checkLoginRateLimitAndGetCurrentRequest(string $username = ''): Request
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            throw new InvalidCredentialsUserError('Request is not available.');
        }

        $request->attributes->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        $limit = $this->loginRateLimiter->peek($request);

        if (!$limit->isAccepted() || $limit->getRemainingTokens() === 0) {
            throw new TooManyLoginAttemptsUserError('Too many login attempts. Try again later.');
        }

        return $request;
    }
}
