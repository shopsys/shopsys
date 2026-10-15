<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Fixtures;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContainerContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

#[ContentBlock(type: 'columns', profiles: [ContentProfileEnum::WEB, ContentProfileEnum::MAIL])]
class DummyColumnsBlock extends AbstractContentBlock implements ContainerContentBlockInterface
{
    public const string LAYOUT_TWO_COLUMNS = '1/2-1/2';
    public const string LAYOUT_THREE_COLUMNS = '1/3-1/3-1/3';

    #[Override]
    public function getLabel(): string
    {
        return 'Columns';
    }

    #[Override]
    public function getCategory(): string
    {
        return static::CATEGORY_LAYOUT;
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ChoiceField::create('layout', [static::LAYOUT_TWO_COLUMNS, static::LAYOUT_THREE_COLUMNS])
                ->default(static::LAYOUT_TWO_COLUMNS),
        ];
    }

    #[Override]
    public function getSlotCount(array $data): int
    {
        return $data['layout'] === static::LAYOUT_THREE_COLUMNS ? 3 : 2;
    }
}
