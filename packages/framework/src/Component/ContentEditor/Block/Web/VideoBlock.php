<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ContentBlock(type: 'video')]
class VideoBlock extends AbstractContentBlock
{
    public function __construct(
        protected readonly VideoUrlParser $videoUrlParser,
    ) {
    }

    #[Override]
    public function getLabel(): string
    {
        return t('Video');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:brand-youtube';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(UrlField::class)::create('url')
                ->label(t('Video URL'))
                ->help(t('YouTube or Vimeo link'))
                ->required()
                ->constraints([new Callback($this->validateUrl(...))]),
        ];
    }

    #[Override]
    public function export(array $data, int $domainId): array
    {
        $url = $data['url'] ?? null;
        $video = is_string($url) ? $this->videoUrlParser->parse($url) : null;

        $data['provider'] = $video?->provider;
        $data['videoId'] = $video?->videoId;
        $data['embedUrl'] = $video?->embedUrl;

        return $data;
    }

    protected function validateUrl(mixed $url, ExecutionContextInterface $context): void
    {
        if (is_string($url) && $url !== '' && $this->videoUrlParser->parse($url) === null) {
            $context->addViolation('Enter a YouTube or Vimeo video URL.');
        }
    }
}
