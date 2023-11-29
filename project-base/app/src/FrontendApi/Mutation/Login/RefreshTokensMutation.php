<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Login;

use App\FrontendApi\Mutation\Login\Exception\InvalidRefreshTokenUserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrontendApiBundle\Model\Mutation\Login\RefreshTokensMutation as BaseRefreshTokensMutation;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;

/**
 * @property \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
 * @property \App\FrontendApi\Model\Token\TokenFacade $tokenFacade
 * @method __construct(\App\FrontendApi\Model\Token\TokenFacade $tokenFacade, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \App\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade)
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 */
class RefreshTokensMutation extends BaseRefreshTokensMutation
{
    /**
     * {@inheritdoc}
     */
    public function refreshTokensMutation(Argument $argument): array
    {
        $refreshToken = $argument['input']['refreshToken'];
        $token = $this->tokenFacade->getTokenByString($refreshToken);

        $userUuid = $token->claims()->get(FrontendApiUser::CLAIM_UUID);

        try {
            /** @var \App\Model\Customer\User\CustomerUser $customerUser */
            $customerUser = $this->customerUserFacade->getByUuid($userUuid);
        } catch (CustomerUserNotFoundException $customerUserNotFoundException) {
            throw new InvalidTokenUserMessageException('Token is not valid.');
        }

        $tokenSecretChain = $token->claims()->get(FrontendApiUser::CLAIM_SECRET_CHAIN);
        $deviceId = $token->claims()->get(FrontendApiUser::CLAIM_DEVICE_ID);

        if ($tokenSecretChain === null || $deviceId === null) {
            throw new InvalidTokenUserMessageException('Token is not valid.');
        }

        $customerUserValidRefreshTokenChain = $this->customerUserRefreshTokenChainFacade->findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId(
            $customerUser,
            $tokenSecretChain,
            $deviceId,
        );

        if ($customerUserValidRefreshTokenChain === null) {
            throw new InvalidRefreshTokenUserError('Token is not valid.');
        }

        $tokens = [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString(
                $customerUser,
                $customerUserValidRefreshTokenChain->getDeviceId(),
                $customerUserValidRefreshTokenChain->getAdministrator(),
            ),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString(
                $customerUser,
                $customerUserValidRefreshTokenChain->getDeviceId(),
                $customerUserValidRefreshTokenChain->getAdministrator(),
            ),
        ];

        $this->customerUserRefreshTokenChainFacade->removeCustomerRefreshTokenChain(
            $customerUserValidRefreshTokenChain,
        );

        return $tokens;
    }
}
