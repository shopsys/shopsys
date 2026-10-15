<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;

#[ContentBlock(type: 'text')]
class DummyTextBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return 'Text';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            WysiwygField::create('html'),
        ];
    }
}
