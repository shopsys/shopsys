<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Error;

interface UserErrorWithCodeInterface
{
    /**
     * @return string
     */
    public function getUserErrorCode(): string;
}
