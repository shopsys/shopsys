<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use Lcobucci\JWT\Token\DataSet;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;

class RefreshTokensMutation extends AbstractMutation
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     */
    public function __construct(
        protected readonly TokenFacade $tokenFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array
     */
    public function refreshTokensMutation(Argument $argument): array
    {
        $refreshToken = $argument['input']['refreshToken'];
        $token = $this->tokenFacade->getTokenByString($refreshToken);

        $this->assertClaimsExists($token->claims());

        $userUuid = $token->claims()->get(FrontendApiUser::CLAIM_UUID);

        try {
            $customerUser = $this->customerUserFacade->getByUuid($userUuid);
        } catch (CustomerUserNotFoundException $customerUserNotFoundException) {
            throw new InvalidTokenUserMessageException();
        }

        $tokenSecretChain = $token->claims()->get(FrontendApiUser::CLAIM_SECRET_CHAIN);
        $deviceId = $token->claims()->get(FrontendApiUser::CLAIM_DEVICE_ID);

        $customerUserValidRefreshTokenChain = $this->customerUserRefreshTokenChainFacade->findCustomersTokenChainByCustomerUserAndSecretChainAndDeviceId(
            $customerUser,
            $tokenSecretChain,
            $deviceId,
        );

        if ($customerUserValidRefreshTokenChain === null) {
            throw new InvalidTokenUserMessageException();
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

    /**
     * @param \Lcobucci\JWT\Token\DataSet $claims
     */
    protected function assertClaimsExists(DataSet $claims): void
    {
        if (!$claims->has(FrontendApiUser::CLAIM_UUID) || !$claims->has(FrontendApiUser::CLAIM_SECRET_CHAIN) || !$claims->has(FrontendApiUser::CLAIM_DEVICE_ID)) {
            throw new InvalidTokenUserMessageException();
        }
    }
}
