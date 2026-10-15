<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ContainerContentBlockInterface;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;

/**
 * The layout is given by the type of the block, so there is nothing to edit and the number of slots never changes
 */
abstract class AbstractColumnsBlock extends AbstractContentBlock implements ContainerContentBlockInterface
{
    /**
     * Widths of the columns separated by "-", e.g. "1/3-2/3"
     */
    protected const string LAYOUT = '';

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
        return [];
    }

    #[Override]
    public function getSlotCount(array $data): int
    {
        return count(explode('-', static::LAYOUT));
    }

    #[Override]
    public function export(array $data, ExportContext $context): array
    {
        return $data + ['layout' => static::LAYOUT];
    }
}
