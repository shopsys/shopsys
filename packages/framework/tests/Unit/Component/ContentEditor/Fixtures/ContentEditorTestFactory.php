<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures;

use InvalidArgumentException;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContentBlockRegistry;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\FieldInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Sanitizer\ContentHtmlSanitizer;
use Shopsys\FrameworkBundle\Component\Html\HtmlContentProcessor;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\Validator\Validation;

class ContentEditorTestFactory
{
    public static function createRegistry(): ContentBlockRegistry
    {
        return new ContentBlockRegistry(
            [new DummyTextBlock(), new DummyImageBlock(), new DummyMailTextBlock(), new DummyColumnsBlock()],
            [],
        );
    }

    /**
     * Mirrors the "content_editor" sanitizer configured by ShopsysFrameworkExtension::prepend()
     */
    public static function createSanitizer(): ContentHtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig())->allowRelativeLinks()->withMaxInputLength(-1);

        foreach (ContentHtmlSanitizer::ALLOWED_ELEMENTS as $element => $attributes) {
            $config = $config->allowElement($element, $attributes);
        }

        foreach (ContentHtmlSanitizer::BLOCKED_ELEMENTS as $element) {
            $config = $config->blockElement($element);
        }

        return new ContentHtmlSanitizer(new HtmlSanitizer($config), new HtmlContentProcessor());
    }

    public static function getField(ContentBlockInterface $block, string $name): FieldInterface
    {
        foreach ($block->getFields() as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        throw new InvalidArgumentException(sprintf('Block "%s" has no field "%s"', $block->getType(), $name));
    }

    /**
     * @return string[] messages of the violations of the field constraints
     */
    public static function validateField(ContentBlockInterface $block, string $fieldName, mixed $value): array
    {
        $violations = Validation::createValidator()->validate($value, self::getField($block, $fieldName)->getConstraints());
        $messages = [];

        foreach ($violations as $violation) {
            $messages[] = (string)$violation->getMessage();
        }

        return $messages;
    }
}
