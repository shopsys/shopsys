<?php

declare(strict_types=1);

namespace App\Model\Product\Comparison\Exception;

use GraphQL\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ComparisonException extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'invalid-argument';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
