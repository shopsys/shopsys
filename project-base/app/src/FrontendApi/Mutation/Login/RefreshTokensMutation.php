<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Login;

use Shopsys\FrontendApiBundle\Model\Mutation\Login\RefreshTokensMutation as BaseRefreshTokensMutation;

/**
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method __construct(\App\FrontendApi\Model\Token\TokenFacade $tokenFacade, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade, \Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory $tokensDataFactory)
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 */
class RefreshTokensMutation extends BaseRefreshTokensMutation
{
}
