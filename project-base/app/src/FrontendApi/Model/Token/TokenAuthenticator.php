<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Token;

use Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator as BaseTokenAuthenticator;

/**
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method __construct(\App\FrontendApi\Model\Token\TokenFacade $tokenFacade, \Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider $frontendApiUserProvider, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade)
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 */
class TokenAuthenticator extends BaseTokenAuthenticator
{
}
