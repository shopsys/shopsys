<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ImageBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

/**
 * Mail clients do not scale images, so the image is limited to the width of the mail content
 */
#[ContentBlock(type: 'mail-image', profiles: [ContentProfileEnum::MAIL])]
class MailImageBlock extends ImageBlock
{
    protected const int MAX_WIDTH = 600;

    #[Override]
    public function export(array $data, int $domainId): array
    {
        $data = parent::export($data, $domainId);

        if (isset($data['width'])) {
            // the height is left to the renderer, so the image keeps its proportions
            $data['width'] = min($data['width'], static::MAX_WIDTH);
            unset($data['height']);
        }

        return $data;
    }

    #[Override]
    protected function createLinkField(): UrlField
    {
        return parent::createLinkField()->allowVariables();
    }
}
