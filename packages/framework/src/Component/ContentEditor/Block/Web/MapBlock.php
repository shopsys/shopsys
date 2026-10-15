<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Symfony\Component\Validator\Constraints\Regex;

#[ContentBlock(type: 'map')]
class MapBlock extends AbstractContentBlock
{
    protected const string EMBED_URL_PATTERN = '~^https://(?:www\.google\.com/maps/embed|maps\.google\.com/maps\?\S*output=embed)~i';

    #[Override]
    public function getLabel(): string
    {
        return t('Map');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:map';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(UrlField::class)::create('url')
                ->label(t('Map embed URL'))
                ->help(t('Google Maps embed link'))
                ->required()
                ->constraints([new Regex(pattern: static::EMBED_URL_PATTERN, message: 'Enter a Google Maps embed URL.')]),
        ];
    }
}
