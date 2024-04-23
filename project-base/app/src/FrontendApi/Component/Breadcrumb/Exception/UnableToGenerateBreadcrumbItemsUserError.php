<?php

declare(strict_types=1);

namespace App\FrontendApi\Component\Breadcrumb\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class UnableToGenerateBreadcrumbItemsUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'unable-to-generate-breadcrumb-items';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
