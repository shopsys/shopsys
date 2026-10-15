<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\MediaField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;

#[ContentBlock(type: 'image')]
class DummyImageBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return 'Image';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            MediaField::create('mediaId'),
            TextField::create('alt'),
            ChoiceField::create('position', ['left', 'center', 'right'])->default('center'),
        ];
    }
}
