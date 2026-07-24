<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Security;

use Closure;
use LogicException;
use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrontendApiBundle\Model\Security\RefreshTokenRedisCacheFacade;
use Shopsys\FrontendApiBundle\Model\Security\RefreshTokenRotationFacade;
use Shopsys\FrontendApiBundle\Model\Security\TokensData;
use Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;

final class TestRefreshTokenRotationFacade extends RefreshTokenRotationFacade
{
    /**
     * @var \Closure(): \Shopsys\FrontendApiBundle\Model\Security\TokensData
     */
    private Closure $generateTokensData;

    /**
     * @param (callable(): \Shopsys\FrontendApiBundle\Model\Security\TokensData)|null $generateTokensData
     */
    public function __construct(
        TokenFacade $tokenFacade,
        CustomerUserFacade $customerUserFacade,
        CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
        TokensDataFactory $tokensDataFactory,
        RefreshTokenRedisCacheFacade $refreshTokenRedisCacheFacade,
        ?callable $generateTokensData = null,
    ) {
        parent::__construct(
            $tokenFacade,
            $customerUserFacade,
            $customerUserRefreshTokenChainFacade,
            $tokensDataFactory,
            $refreshTokenRedisCacheFacade,
        );

        $this->generateTokensData = Closure::fromCallable(
            $generateTokensData ?? static fn (): TokensData => throw new LogicException(
                'Tokens data should not be generated.',
            ),
        );
    }

    public function getTokensDataOrGenerateForTest(
        CustomerUser $customerUser,
        string $tokenSecretChain,
        string $deviceId,
    ): TokensData {
        return $this->getTokensDataOrGenerate($customerUser, $tokenSecretChain, $deviceId);
    }

    #[Override]
    protected function generateTokensData(
        CustomerUser $customerUser,
        string $tokenSecretChain,
        string $deviceId,
    ): TokensData {
        return ($this->generateTokensData)();
    }
}
