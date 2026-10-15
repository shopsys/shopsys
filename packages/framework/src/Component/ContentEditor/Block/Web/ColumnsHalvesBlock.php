<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;

#[ContentBlock(type: 'columns-1-1')]
class ColumnsHalvesBlock extends AbstractColumnsBlock
{
    protected const string LAYOUT = '1/2-1/2';

    #[Override]
    public function getLabel(): string
    {
        return t('Two columns');
    }
}
