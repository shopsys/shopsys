<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\MediaField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Media\Exception\MediaNotFoundException;
use Shopsys\FrameworkBundle\Model\Media\Media;
use Shopsys\FrameworkBundle\Model\Media\MediaFacade;

#[ContentBlock(type: 'image')]
class ImageBlock extends AbstractContentBlock
{
    public const string POSITION_LEFT = 'left';
    public const string POSITION_CENTER = 'center';
    public const string POSITION_RIGHT = 'right';

    protected const ?int MAX_WIDTH = null;

    public function __construct(
        protected readonly MediaFacade $mediaFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function getLabel(): string
    {
        return t('Image');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:photo';
    }

    // mediaId is not required, an image converted from legacy content has only a url
    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(MediaField::class)::create('mediaId')->label(t('Image')),
            ExtendedClassNameResolver::resolve(TextField::class)::create('alt')->label(t('Alternative text')),
            ExtendedClassNameResolver::resolve(ChoiceField::class)::create('position', [static::POSITION_LEFT, static::POSITION_CENTER, static::POSITION_RIGHT])
                ->label(t('Position'))
                ->default(static::POSITION_CENTER),
            $this->createLinkField(),
        ];
    }

    #[Override]
    public function export(array $data, int $domainId): array
    {
        $media = $this->findMedia($data['mediaId'] ?? null);

        if ($media !== null) {
            $data['url'] = $this->getMediaUrl($media, $domainId);
            $data['width'] = $media->getWidth();
            $data['height'] = $media->getHeight();

            return $data;
        }

        // legacy images (created before the media library) and images whose media was deleted
        // keep the stored url, so the storefront still shows something
        $url = $data['url'] ?? null;
        $hasUrl = is_string($url) && $url !== '';
        $data['url'] = $hasUrl ? $url : null;

        if ($hasUrl) {
            $data['legacy'] = true;
        }

        return $data;
    }

    protected function getMediaUrl(Media $media, int $domainId): string
    {
        return $this->mediaFacade->getImageUrl($this->domain->getDomainConfigById($domainId), $media, static::MAX_WIDTH);
    }

    protected function createLinkField(): UrlField
    {
        return ExtendedClassNameResolver::resolve(UrlField::class)::create('link')->label(t('Link'));
    }

    protected function findMedia(mixed $mediaId): ?Media
    {
        if (!is_int($mediaId)) {
            return null;
        }

        try {
            return $this->mediaFacade->getById($mediaId);
        } catch (MediaNotFoundException) {
            return null;
        }
    }
}
