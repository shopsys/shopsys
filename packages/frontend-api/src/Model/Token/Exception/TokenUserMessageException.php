<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token\Exception;

use GraphQL\Error\ClientAware;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class TokenUserMessageException extends CustomUserMessageAuthenticationException implements ClientAware
{
    /**
     * @return bool
     */
    public function isClientSafe(): bool
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
}
