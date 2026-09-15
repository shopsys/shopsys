<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Customer\User;

use Shopsys\FrontendApiBundle\Model\Mutation\Customer\User\CustomerUserPasswordRecoveryMutation as BaseCustomerUserPasswordRecoveryMutation;

/**
 * @property \App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade
 * @property \App\FrontendApi\Mutation\Login\LoginMutation $loginMutation
 * @method __construct(\App\Model\Customer\User\CustomerUserPasswordFacade $customerUserPasswordFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\FrontendApi\Mutation\Login\LoginMutation $loginMutation, \Overblog\GraphQLBundle\Definition\ArgumentFactory $argumentFactory, \Symfony\Component\RateLimiter\RateLimiterFactoryInterface $passwordRecoveryIpRateLimiter, \Symfony\Component\RateLimiter\RateLimiterFactoryInterface $passwordRecoveryEmailRateLimiter, \Symfony\Component\HttpFoundation\RequestStack $requestStack)
 */
class CustomerUserPasswordRecoveryMutation extends BaseCustomerUserPasswordRecoveryMutation
{
}
