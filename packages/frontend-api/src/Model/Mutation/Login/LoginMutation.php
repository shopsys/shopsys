<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeDataFactory;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\InvalidCredentialsUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\Exception\TooManyLoginAttemptsUserError;
use Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade;
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
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade $customerUserLoginTypeFacade
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade $loginAsUserFacade
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
        protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade,
        protected readonly CustomerUserLoginTypeDataFactory $customerUserLoginTypeDataFactory,
        protected readonly LoginAsUserFacade $loginAsUserFacade,
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
            $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($input['email']);
        } catch (UserNotFoundException) {
            throw new InvalidCredentialsUserError('Log in failed.');
        }

        if (!$this->userPasswordHasher->isPasswordValid($customerUser, $input['password'])) {
            throw new InvalidCredentialsUserError('Log in failed.');
        }

        $this->loginRateLimiter->reset($this->requestStack->getCurrentRequest());

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
}
