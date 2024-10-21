<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Breadcrumb\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class UnableToGenerateBreadcrumbItemsUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const string CODE = 'unable-to-generate-breadcrumb-items';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
