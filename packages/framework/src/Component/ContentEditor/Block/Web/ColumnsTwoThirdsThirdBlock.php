<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractColumnsBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;

#[ContentBlock(type: 'columns-2-1')]
class ColumnsTwoThirdsThirdBlock extends AbstractColumnsBlock
{
    protected const string LAYOUT = '2/3-1/3';

    #[Override]
    public function getLabel(): string
    {
        return t('Wide and narrow column');
    }
}
