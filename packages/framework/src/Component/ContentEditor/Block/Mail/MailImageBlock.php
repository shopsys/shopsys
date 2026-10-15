<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web\ImageBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\MediaField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;
use Shopsys\FrameworkBundle\Model\Media\Media;

/**
 * Mail clients do not scale images, so the image is limited to the width of the mail content
 */
#[ContentBlock(type: 'mail-image', profiles: [ContentProfileEnum::MAIL])]
class MailImageBlock extends ImageBlock
{
    protected const int MAX_WIDTH = 600;

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(MediaField::class)::create('mediaId')->label(t('Image')),
            ExtendedClassNameResolver::resolve(TextField::class)::create('alt')->label(t('Alternative text')),
            ExtendedClassNameResolver::resolve(ChoiceField::class)::create('position', [static::POSITION_LEFT, static::POSITION_CENTER, static::POSITION_RIGHT])
                ->label(t('Position'))
                ->default(static::POSITION_CENTER),
            ExtendedClassNameResolver::resolve(UrlField::class)::create('link')->label(t('Link'))->allowVariables(),
        ];
    }

    #[Override]
    public function export(array $data, ExportContext $context): array
    {
        $data = parent::export($data, $context);

        if (isset($data['width'])) {
            // the height is left to the renderer, so the image keeps its proportions
            $data['width'] = min($data['width'], static::MAX_WIDTH);
            unset($data['height']);
        }

        return $data;
    }

    #[Override]
    protected function getMediaUrl(Media $media, ExportContext $context): string
    {
        return $this->mediaFacade->getImageUrl($this->domain->getDomainConfigById($context->domainId), $media, static::MAX_WIDTH);
    }
}
