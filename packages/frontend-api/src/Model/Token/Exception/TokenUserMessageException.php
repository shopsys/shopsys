<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token\Exception;

use GraphQL\Error\ClientAware;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class TokenUserMessageException extends CustomUserMessageAuthenticationException implements ClientAware, UserErrorWithCodeInterface
{
    protected const CODE = 'invalid-token';

    /**
     * @return bool
     */
    public function isClientSafe()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return 'token';
    }

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
