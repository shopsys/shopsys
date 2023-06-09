<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TransportCanBeUsed extends Constraint
{
    public const PRICES_DOES_NOT_MATCH_ERROR = 'c4ad85a0-9e32-4540-8491-9d899f3073bc';
    public const TRANSPORT_NOT_FOUND_ERROR = '1f2d316b-3edd-4869-bba6-b234856a7783';

    public string $pricesDoesNotMatchMessage = 'Price for transport {{ uuid }} has changed';

    public string $transportNotFoundMessage = 'Transport {{ uuid }} doesn\'t exist';

    /**
     * @var array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $errorNames = [
        self::PRICES_DOES_NOT_MATCH_ERROR => 'PRICES_DOES_NOT_MATCH_ERROR',
        self::TRANSPORT_NOT_FOUND_ERROR => 'TRANSPORT_NOT_FOUND_ERROR',
    ];
}
