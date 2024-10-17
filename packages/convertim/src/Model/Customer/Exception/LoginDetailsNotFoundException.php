<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Customer\Exception;

use Shopsys\ConvertimBundle\Model\Convertim\Exception\ConvertimException;

class LoginDetailsNotFoundException extends ConvertimException
{
    /**
     * @param string $email
     * @param \Exception|null $previous
     */
    public function __construct(string $email, $previous = null)
    {
        parent::__construct('Login details not found', ['email' => $email], 404, $previous);
    }
}
