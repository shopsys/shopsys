<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

interface UserErrorWithCodeInterface
{
    public function getUserErrorCode(): string;
}
