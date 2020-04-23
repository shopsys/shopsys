<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use GraphQL\Error\UserError;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;

class RefreshTokensMutation implements MutationInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrontendApiBundle\Model\Token\TokenFacade
     */
    protected $tokenFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    protected $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade
     */
    protected $customerUserRefreshTokenChainFacade;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
     */
    public function __construct(
        TokenFacade $tokenFacade,
        CustomerUserFacade $customerUserFacade,
        CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade
    ) {
        $this->tokenFacade = $tokenFacade;
        $this->customerUserFacade = $customerUserFacade;
        $this->customerUserRefreshTokenChainFacade = $customerUserRefreshTokenChainFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array
     */
    public function refreshTokens(Argument $argument): array
    {
        $refreshToken = $argument['input']['refreshToken'];
        $token = $this->tokenFacade->getTokenByString($refreshToken);

        $userUuid = $token->getClaim('uuid');

        try {
            $customerUser = $this->customerUserFacade->getByUuid($userUuid);
        } catch (CustomerUserNotFoundException $customerUserNotFoundException) {
            throw new InvalidTokenUserMessageException('Token is not valid.');
        }

        $tokenSecretChain = $token->getClaim('secretChain');
        $customerUserValidRefreshTokenChain = $this->customerUserRefreshTokenChainFacade->findCustomersTokenChainByCustomerUserAndSecretChain($customerUser, $tokenSecretChain);

        if ($customerUserValidRefreshTokenChain === null) {
            throw new UserError('Token is not valid.');
        }

        return [
            'accessToken' => $this->tokenFacade->createAccessTokenAsString($customerUser, $customerUserValidRefreshTokenChain->getDeviceId()),
            'refreshToken' => $this->tokenFacade->createRefreshTokenAsString($customerUser, $customerUserValidRefreshTokenChain->getDeviceId()),
        ];
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'refreshTokens' => 'refresh_tokens',
        ];
    }
}
