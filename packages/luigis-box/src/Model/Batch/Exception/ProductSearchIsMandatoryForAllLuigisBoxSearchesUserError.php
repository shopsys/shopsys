<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch\Exception;

use GraphQL\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductSearchIsMandatoryForAllLuigisBoxSearchesUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'product-search-is-mandatory-for-all-luigis-box-searches';

    public function __construct()
    {
        parent::__construct('You must to do a product search to be able to search in other entities while using Luigi\'s Box.');
    }

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
