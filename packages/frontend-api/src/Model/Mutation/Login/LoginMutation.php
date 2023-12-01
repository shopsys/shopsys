<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\InvalidAccountOrPasswordUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\TooManyLoginAttemptsUserError;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\RateLimiter\DefaultLoginRateLimiter;

class LoginMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider
     * @param \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     * @param \Symfony\Component\Security\Http\RateLimiter\DefaultLoginRateLimiter $loginRateLimiter
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade
     */
    public function __construct(
        protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
        protected readonly UserPasswordHasherInterface $userPasswordHasher,
        protected readonly TokenFacade $tokenFacade,
        protected readonly DefaultLoginRateLimiter $loginRateLimiter,
        protected readonly RequestStack $requestStack,
        protected readonly ProductListFacade $productListFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string[]
     */
    public function loginMutation(Argument $argument): array
    {
        $input = $argument['input'];

        if ($this->loginRateLimiter !== null) {
            $limit = $this->loginRateLimiter->consume($this->requestStack->getCurrentRequest());

            if (!$limit->isAccepted()) {
                throw new TooManyLoginAttemptsUserError('Too many login attempts. Try again later.');
            }
        }

        try {
            $user = $this->frontendCustomerUserProvider->loadUserByUsername($input['email']);
        } catch (UserNotFoundException $e) {
            throw new InvalidAccountOrPasswordUserError($e->getMessage());
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $input['password'])) {
            throw new InvalidAccountOrPasswordUserError('Invalid password.');
        }

        $deviceId = Uuid::uuid4()->toString();

        $this->loginRateLimiter->reset($this->requestStack->getCurrentRequest());

        $this->productListFacade->mergeProductListsToCustomerUser($input['productListsUuids'], $user);

        return [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString($user, $deviceId),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($user, $deviceId),
        ];
    }
}
