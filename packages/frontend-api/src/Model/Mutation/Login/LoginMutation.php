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

        $currentRequest = $this->checkLoginRateLimitAndGetCurrentRequest();

        try {
            $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($input['email']);
        } catch (UserNotFoundException) {
            throw new InvalidCredentialsUserError('Log in failed.');
        }

        if (!$this->userPasswordHasher->isPasswordValid($customerUser, $input['password'])) {
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
            throw new InvalidCredentialsUserError('Invalid or expired exchange token.');
        }
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
