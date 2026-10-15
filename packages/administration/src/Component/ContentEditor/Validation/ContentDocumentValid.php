<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\ContentEditor\Validation;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * Fails when any block of the document does not satisfy the constraints of its fields, see ContentDocumentType
 */
class ContentDocumentValid extends Constraint
{
    /**
     * @param array<int, array{label: string, placeholder: string, isRequired: bool, isImage: bool}> $variables placeholders of the editor, see ContentEditorType
     * @param string[]|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        public array $variables = [],
        public string $message = 'The content has {{ count }} invalid block(s), fix them in the editor',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }
}
