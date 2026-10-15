<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type\Transformer;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Sanitizer\ContentHtmlSanitizer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements \Symfony\Component\Form\DataTransformerInterface<string|null, string|null>
 */
final class SanitizedHtmlTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly ContentHtmlSanitizer $contentHtmlSanitizer,
    ) {
    }

    #[Override]
    public function transform(mixed $value): ?string
    {
        return $value;
    }

    #[Override]
    public function reverseTransform(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->contentHtmlSanitizer->sanitize($value);
    }
}
