<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use Overblog\GraphQLBundle\Definition\Argument;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\InvalidCredentialsUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\TooManyLoginAttemptsUserError;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultData;
use Shopsys\FrontendApiBundle\Model\Security\LoginResultDataFactory;
use Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory;
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
     * @param \Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade $mergeCartFacade
     * @param \Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory $tokensDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginResultDataFactory $loginResultDataFactory
     */
    public function __construct(
        protected readonly FrontendCustomerUserProvider $frontendCustomerUserProvider,
        protected readonly UserPasswordHasherInterface $userPasswordHasher,
        protected readonly TokenFacade $tokenFacade,
        protected readonly DefaultLoginRateLimiter $loginRateLimiter,
        protected readonly RequestStack $requestStack,
        protected readonly ProductListFacade $productListFacade,
        protected readonly MergeCartFacade $mergeCartFacade,
        protected readonly TokensDataFactory $tokensDataFactory,
        protected readonly LoginResultDataFactory $loginResultDataFactory,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrontendApiBundle\Model\Security\LoginResultData
     */
    public function loginMutation(Argument $argument): LoginResultData
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
            throw new InvalidCredentialsUserError('Log in failed.');
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $input['password'])) {
            throw new InvalidCredentialsUserError('Log in failed.');
        }

        if (array_key_exists('cartUuid', $input) && $input['cartUuid'] !== null) {
            if (array_key_exists('shouldOverwriteCustomerUserCart', $input) && $input['shouldOverwriteCustomerUserCart']) {
                $this->mergeCartFacade->overwriteCustomerCartWithCartByUuid($input['cartUuid'], $user);
            } else {
                $this->mergeCartFacade->mergeCartByUuidToCustomerCart($input['cartUuid'], $user);
            }
        }

        $deviceId = Uuid::uuid4()->toString();

        $this->loginRateLimiter->reset($this->requestStack->getCurrentRequest());

        if (array_key_exists('productListsUuids', $input) && $input['productListsUuids']) {
            $this->productListFacade->mergeProductListsToCustomerUser($input['productListsUuids'], $user);
        }

        return $this->loginResultDataFactory->create(
            $this->tokensDataFactory->create(
                $this->tokenFacade->createAccessTokenAsString($user, $deviceId),
                $this->tokenFacade->createRefreshTokenAsString($user, $deviceId),
            ),
            $this->mergeCartFacade->shouldShowCartMergeInfo(),
        );
    }
}
