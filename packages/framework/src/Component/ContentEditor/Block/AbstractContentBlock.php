<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block;

use Override;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Webmozart\Assert\Assert;

abstract class AbstractContentBlock implements ContentBlockInterface
{
    public const string CATEGORY_CONTENT = 'content';
    public const string CATEGORY_LAYOUT = 'layout';

    protected ?ContentBlock $contentBlockAttribute = null;

    #[Override]
    public function getType(): string
    {
        return $this->getContentBlockAttribute()->type;
    }

    #[Override]
    public function getProfiles(): array
    {
        return $this->getContentBlockAttribute()->profiles;
    }

    #[Override]
    public function getIcon(): ?string
    {
        return null;
    }

    #[Override]
    public function getCategory(): string
    {
        return static::CATEGORY_CONTENT;
    }

    #[Override]
    public function getDefaultData(): array
    {
        $defaultData = [];

        foreach ($this->getFields() as $field) {
            $defaultData[$field->getName()] = $field->getDefault();
        }

        return $defaultData;
    }

    #[Override]
    public function export(array $data, ExportContext $context): array
    {
        return $data;
    }

    #[Override]
    public function extractText(array $data): string
    {
        $texts = [];

        foreach ($this->getFields() as $field) {
            $text = $field->extractText($data[$field->getName()] ?? null);

            if ($text !== '') {
                $texts[] = $text;
            }
        }

        return implode("\n", $texts);
    }

    protected function getContentBlockAttribute(): ContentBlock
    {
        if ($this->contentBlockAttribute === null) {
            $attributes = (new ReflectionClass(static::class))->getAttributes(ContentBlock::class);
            Assert::notEmpty($attributes, sprintf('Class "%s" must be annotated with #[ContentBlock]', static::class));

            $this->contentBlockAttribute = $attributes[0]->newInstance();
        }

        return $this->contentBlockAttribute;
    }
}
