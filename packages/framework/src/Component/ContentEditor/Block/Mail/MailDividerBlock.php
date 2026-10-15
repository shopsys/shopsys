<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

#[ContentBlock(type: 'mail-divider', profiles: [ContentProfileEnum::MAIL])]
class MailDividerBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return t('Divider');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:minus';
    }

    #[Override]
    public function getFields(): array
    {
        return [];
    }
}
