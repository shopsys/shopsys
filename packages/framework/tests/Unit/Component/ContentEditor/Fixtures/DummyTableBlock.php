<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TableCellsField;

#[ContentBlock(type: 'table')]
class DummyTableBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return 'Table';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            TableCellsField::create('cells'),
        ];
    }
}
