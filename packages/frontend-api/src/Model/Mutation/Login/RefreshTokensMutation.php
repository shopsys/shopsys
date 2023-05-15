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

        $userUuid = $token->claims()->get('uuid');

        try {
            $customerUser = $this->customerUserFacade->getByUuid($userUuid);
        } catch (CustomerUserNotFoundException $customerUserNotFoundException) {
            throw new InvalidTokenUserMessageException();
        }

        $tokenSecretChain = $token->claims()->get('secretChain');
        $customerUserValidRefreshTokenChain = $this->customerUserRefreshTokenChainFacade->findCustomersTokenChainByCustomerUserAndSecretChain(
            $customerUser,
            $tokenSecretChain,
        );

        if ($customerUserValidRefreshTokenChain === null) {
            throw new InvalidTokenUserMessageException();
        }

        return [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString(
                $customerUser,
                $customerUserValidRefreshTokenChain->getDeviceId(),
            ),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString(
                $customerUser,
                $customerUserValidRefreshTokenChain->getDeviceId(),
            ),
        ];
    }

    /**
     * @param \Lcobucci\JWT\Token\DataSet $claims
     */
    protected function assertClaimsExists(DataSet $claims): void
    {
        if (!$claims->has('uuid') || !$claims->has('secretChain')) {
            throw new InvalidTokenUserMessageException();
        }
    }
}
