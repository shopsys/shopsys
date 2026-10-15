<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContainerContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;

#[ContentBlock(type: 'columns')]
class ColumnsBlock extends AbstractContentBlock implements ContainerContentBlockInterface
{
    public const string LAYOUT_HALVES = '1/2-1/2';
    public const string LAYOUT_THIRD_TWO_THIRDS = '1/3-2/3';
    public const string LAYOUT_TWO_THIRDS_THIRD = '2/3-1/3';
    public const string LAYOUT_THIRDS = '1/3-1/3-1/3';

    #[Override]
    public function getLabel(): string
    {
        return t('Columns');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:columns';
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
            ExtendedClassNameResolver::resolve(ChoiceField::class)::create('layout', [
                static::LAYOUT_HALVES,
                static::LAYOUT_THIRD_TWO_THIRDS,
                static::LAYOUT_TWO_THIRDS_THIRD,
                static::LAYOUT_THIRDS,
            ])
                ->label(t('Layout'))
                ->default(static::LAYOUT_HALVES),
        ];
    }

    #[Override]
    public function getSlotCount(array $data): int
    {
        return count(explode('-', $data['layout']));
    }
}
