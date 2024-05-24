<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Token;

use Shopsys\FrontendApiBundle\Model\Token\TokenFacade as BaseTokenFacade;

/**
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @method __construct(\Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Lcobucci\JWT\Configuration $jwtConfiguration)
 * @method string createAccessTokenAsString(\App\Model\Customer\User\CustomerUser $customerUser, string $deviceId, \App\Model\Administrator\Administrator|null $administrator = null)
 * @method \Lcobucci\JWT\UnencryptedToken generateRefreshTokenByCustomerUserAndSecretChainAndDeviceId(\App\Model\Customer\User\CustomerUser $customerUser, string $secretChain, string $deviceId)
 * @method string createRefreshTokenAsString(\App\Model\Customer\User\CustomerUser $customerUser, string $deviceId, \App\Model\Administrator\Administrator|null $administrator = null)
 */
class TokenFacade extends BaseTokenFacade
{
}
