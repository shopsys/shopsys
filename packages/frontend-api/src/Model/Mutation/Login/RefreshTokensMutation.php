<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\Login;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Mutation\AbstractMutation;
use Shopsys\FrontendApiBundle\Model\Security\RefreshTokenRotationFacade;
use Shopsys\FrontendApiBundle\Model\Security\TokensData;

class RefreshTokensMutation extends AbstractMutation
{
    public function __construct(
        protected readonly RefreshTokenRotationFacade $refreshTokenRotationFacade,
    ) {
    }

    public function refreshTokensMutation(Argument $argument): TokensData
    {
        return $this->refreshTokenRotationFacade->refreshTokens($argument['input']['refreshToken']);
    }
}
