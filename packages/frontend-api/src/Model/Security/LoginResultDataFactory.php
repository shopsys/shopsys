<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

class LoginResultDataFactory
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Security\TokensData $tokensData
     * @param bool $showCartMergeInfo
     * @param bool $isRegistration
     * @return \Shopsys\FrontendApiBundle\Model\Security\LoginResultData
     */
    public function create(
        TokensData $tokensData,
        bool $showCartMergeInfo,
        bool $isRegistration = false,
    ): LoginResultData {
        return new LoginResultData($tokensData, $showCartMergeInfo, $isRegistration);
    }
}
