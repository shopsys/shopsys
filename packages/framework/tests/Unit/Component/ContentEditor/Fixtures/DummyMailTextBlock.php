<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

#[ContentBlock(type: 'mail-text', profiles: [ContentProfileEnum::MAIL])]
class DummyMailTextBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return 'Mail text';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            WysiwygField::create('html'),
        ];
    }
}
