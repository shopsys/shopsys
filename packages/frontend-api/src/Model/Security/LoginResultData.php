<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

class LoginResultData
{
    public function __construct(
        public readonly TokensData $tokens,
        public readonly bool $showCartMergeInfo,
        public readonly bool $isRegistration = false,
    ) {
    }
}
