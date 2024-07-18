<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Token;

use Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator as BaseTokenAuthenticator;

/**
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method __construct(\App\FrontendApi\Model\Token\TokenFacade $tokenFacade, \Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider $frontendApiUserProvider)
 */
class TokenAuthenticator extends BaseTokenAuthenticator
{
}
