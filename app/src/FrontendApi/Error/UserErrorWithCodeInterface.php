<?php

declare(strict_types=1);

namespace App\FrontendApi\Error;

interface UserErrorWithCodeInterface
{
    /**
     * @return string
     */
    public function getUserErrorCode(): string;
}
