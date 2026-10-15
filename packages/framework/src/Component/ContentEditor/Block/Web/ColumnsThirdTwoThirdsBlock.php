<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;

#[ContentBlock(type: 'columns-1-2')]
class ColumnsThirdTwoThirdsBlock extends AbstractColumnsBlock
{
    protected const string LAYOUT = '1/3-2/3';

    #[Override]
    public function getLabel(): string
    {
        return t('Narrow and wide column');
    }
}
