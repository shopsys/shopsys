<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class ExistingEmail extends Constraint
{
    public const USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR = 'd1bf5f27-fe92-424c-bb58-df90cc7637b1';

    public string $invalidMessage = 'User with provided email address does not exist.';

    /**
     * @var array<string, string>
     */
    protected static $errorNames = [
        self::USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR => 'USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR',
    ];
}
