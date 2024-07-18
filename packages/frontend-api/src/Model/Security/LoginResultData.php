<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

class LoginResultData
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Security\TokensData $tokens
     * @param bool $showCartMergeInfo
     */
    public function __construct(
        public readonly TokensData $tokens,
        public readonly bool $showCartMergeInfo,
    ) {
    }
}
