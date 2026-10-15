<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;

#[ContentBlock(type: 'normalized')]
class DummyNormalizedBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return 'Normalized';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            TextField::create('text'),
        ];
    }

    #[Override]
    public function normalizeData(array $data): array
    {
        $data['textLength'] = strlen((string)$data['text']);

        return $data;
    }
}
