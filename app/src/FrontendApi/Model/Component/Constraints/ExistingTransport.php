<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Component\Constraints;

use Symfony\Component\Validator\Constraint;

class ExistingTransport extends Constraint
{
    public const TRANSPORT_DOES_NOT_EXIST_ERROR = '2414f8de-52fd-4a54-ab07-6f6c9f68e5c9';

    public string $invalidMessage = 'Transport with provided UUID does not exist';

    /**
     * @var array<string, string>
     */
    protected static $errorNames = [
        self::TRANSPORT_DOES_NOT_EXIST_ERROR => 'TRANSPORT_DOES_NOT_EXIST_ERROR',
    ];
}
