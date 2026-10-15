<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractColumnsBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;

#[ContentBlock(type: 'columns-1-1-1')]
class ColumnsThirdsBlock extends AbstractColumnsBlock
{
    protected const string LAYOUT = '1/3-1/3-1/3';

    #[Override]
    public function getLabel(): string
    {
        return t('Three columns');
    }
}
