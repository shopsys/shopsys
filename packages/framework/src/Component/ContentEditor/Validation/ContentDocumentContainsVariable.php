<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Validation;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class ContentDocumentContainsVariable extends Constraint
{
    /**
     * @param string $variable placeholder that has to be used in the document, e.g. "{order_number}"
     * @param string[]|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        public string $variable,
        public string $message = 'Variable {{ needle }} is required',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }
}
