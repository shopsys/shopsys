<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractColumnsBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

#[ContentBlock(type: 'mail-columns', profiles: [ContentProfileEnum::MAIL])]
class MailColumnsBlock extends AbstractColumnsBlock
{
    protected const string LAYOUT = '1/2-1/2';

    #[Override]
    public function getLabel(): string
    {
        return t('Two columns');
    }
}
