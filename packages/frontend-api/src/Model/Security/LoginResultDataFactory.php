<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

class LoginResultDataFactory
{
    public function create(
        TokensData $tokensData,
        bool $showCartMergeInfo,
        bool $isRegistration = false,
    ): LoginResultData {
        return new LoginResultData($tokensData, $showCartMergeInfo, $isRegistration);
    }
}
