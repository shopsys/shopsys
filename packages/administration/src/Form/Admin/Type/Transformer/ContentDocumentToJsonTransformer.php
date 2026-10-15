<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Form\Admin\Type\Transformer;

use InvalidArgumentException;
use JsonException;
use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentFactory;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocumentNormalizer;
use Shopsys\FrameworkBundle\Component\ContentEditor\Document\Exception\UnsupportedContentDocumentVersionException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements \Symfony\Component\Form\DataTransformerInterface<\Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument|null, string>
 */
final class ContentDocumentToJsonTransformer implements DataTransformerInterface
{
    /**
     * @param string[]|null $allowedTypes
     */
    public function __construct(
        private readonly ContentDocumentFactory $contentDocumentFactory,
        private readonly ContentDocumentNormalizer $contentDocumentNormalizer,
        private readonly string $profile,
        private readonly ?array $allowedTypes,
    ) {
    }

    #[Override]
    public function transform(mixed $value): string
    {
        return ($value ?? $this->contentDocumentFactory->createEmpty($this->profile))->toJson();
    }

    #[Override]
    public function reverseTransform(mixed $value): ContentDocument
    {
        if ($value === null || trim($value) === '') {
            return $this->contentDocumentFactory->createEmpty($this->profile);
        }

        try {
            $document = $this->contentDocumentFactory->createFromJson($value);
        } catch (JsonException|InvalidArgumentException|UnsupportedContentDocumentVersionException $exception) {
            throw new TransformationFailedException('Invalid content document', 0, $exception);
        }

        if ($document->profile !== $this->profile) {
            throw new TransformationFailedException(sprintf('Content document of profile "%s" was submitted to "%s" editor', $document->profile, $this->profile));
        }

        return $this->contentDocumentNormalizer->normalize($document, $this->allowedTypes);
    }
}
