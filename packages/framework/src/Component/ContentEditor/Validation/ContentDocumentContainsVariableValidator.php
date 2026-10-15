<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Validation;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentTextExtractor;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\VariableField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContentDocumentContainsVariableValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly ContentBlockRegistry $contentBlockRegistry,
        protected readonly ContentDocumentTextExtractor $contentDocumentTextExtractor,
    ) {
    }

    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContentDocumentContainsVariable) {
            throw new UnexpectedTypeException($constraint, ContentDocumentContainsVariable::class);
        }

        if (!$value instanceof ContentDocument) {
            $this->addViolation($constraint);

            return;
        }

        if (
            str_contains($this->contentDocumentTextExtractor->extractText($value), $constraint->variable)
            || $this->isInLinkOrVariable($value->blocks, $constraint->variable)
        ) {
            return;
        }

        $this->addViolation($constraint);
    }

    /**
     * Links and variables are not part of the text, a link may also be in the markup of a rich text
     *
     * @param \Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentBlockInstance[] $blocks
     */
    protected function isInLinkOrVariable(array $blocks, string $variable): bool
    {
        foreach ($blocks as $block) {
            if ($this->contentBlockRegistry->has($block->type) && $this->hasVariableInFields($block, $variable)) {
                return true;
            }

            foreach ($block->children ?? [] as $slot) {
                if ($this->isInLinkOrVariable($slot, $variable)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function hasVariableInFields(ContentBlockInstance $block, string $variable): bool
    {
        foreach ($this->contentBlockRegistry->get($block->type)->getFields() as $field) {
            $fieldValue = $block->data[$field->getName()] ?? null;

            if (
                ($field instanceof UrlField || $field instanceof VariableField || $field instanceof WysiwygField)
                && is_string($fieldValue)
                && str_contains($fieldValue, $variable)
            ) {
                return true;
            }
        }

        return false;
    }

    protected function addViolation(ContentDocumentContainsVariable $constraint): void
    {
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ needle }}', $constraint->variable)
            ->addViolation();
    }
}
