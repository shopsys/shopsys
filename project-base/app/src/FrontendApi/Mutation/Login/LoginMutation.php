<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Login;

use Shopsys\FrontendApiBundle\Model\Mutation\Login\LoginMutation as BaseLoginMutation;

/**
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider $frontendCustomerUserProvider, \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher, \App\FrontendApi\Model\Token\TokenFacade $tokenFacade, \Symfony\Component\Security\Http\RateLimiter\DefaultLoginRateLimiter $loginRateLimiter, \Symfony\Component\HttpFoundation\RequestStack $requestStack, \Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade $productListFacade, \Shopsys\FrontendApiBundle\Model\Cart\MergeCartFacade $mergeCartFacade, \Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory $tokensDataFactory, \Shopsys\FrontendApiBundle\Model\Security\LoginResultDataFactory $loginResultDataFactory)
 */
class LoginMutation extends BaseLoginMutation
{
}
