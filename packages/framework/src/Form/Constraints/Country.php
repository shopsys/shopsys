<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Form\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Country extends Constraint
{
    public const INVALID_COUNTRY_ERROR = '9080a4de-347f-48c7-a41a-b4cc46a5146d';

    /**
     * @var string
     */
    public $message = 'Country with code {{ country_code }} does not exists. Available country codes are {{ available_country_codes }}.';

    /**
     * @var string[]
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $errorNames = [
        self::INVALID_COUNTRY_ERROR => 'INVALID_COUNTRY_ERROR',
    ];

    /**
     * @var int|null
     */
    public $domainId = null;
}
