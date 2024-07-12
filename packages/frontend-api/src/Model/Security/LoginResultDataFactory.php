<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

class LoginResultDataFactory
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Security\TokensData $tokensData
     * @param bool $showCartMergeInfo
     * @return \Shopsys\FrontendApiBundle\Model\Security\LoginResultData
     */
    public function create(TokensData $tokensData, bool $showCartMergeInfo): LoginResultData
    {
        return new LoginResultData($tokensData, $showCartMergeInfo);
    }
}
