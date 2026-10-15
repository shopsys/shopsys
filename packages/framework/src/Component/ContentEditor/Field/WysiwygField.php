<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;

/**
 * The value is HTML. It is sanitized by ContentDocumentNormalizer, so the field itself has no service dependency.
 */
class WysiwygField extends TextField
{
    /**
     * @var array<int, array{label: string, placeholder: string, isRequired: bool, isImage: bool}>
     */
    protected array $variables = [];

    /**
     * @param array<int, array{label: string, placeholder: string, isRequired: bool, isImage: bool}> $variables placeholders offered in the editor (mail)
     */
    public function variables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }

    // The framework must not depend on the administration bundle, so the type is referenced by name only.
    #[Override]
    public function getFormType(): string
    {
        return 'Shopsys\AdministrationBundle\Form\Admin\Type\WysiwygType';
    }

    #[Override]
    public function getFormOptions(): array
    {
        return parent::getFormOptions() + ['variables' => $this->variables];
    }
}
