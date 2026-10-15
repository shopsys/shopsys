<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\NumberField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ContentBlock(type: 'iframe')]
class IframeBlock extends AbstractContentBlock
{
    public const int DEFAULT_HEIGHT = 400;

    /**
     * @param string[] $embedHosts
     */
    public function __construct(
        protected readonly array $embedHosts,
    ) {
    }

    #[Override]
    public function getLabel(): string
    {
        return t('Embedded content');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:code';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(UrlField::class)::create('url')
                ->label(t('URL'))
                ->help(t('Only addresses of allowed hosts can be embedded'))
                ->required()
                ->constraints([new Callback($this->validateUrl(...))]),
            ExtendedClassNameResolver::resolve(NumberField::class)::create('height')
                ->label(t('Height (px)'))
                ->default(static::DEFAULT_HEIGHT)
                ->constraints([new Range(min: 100, max: 2000)]),
        ];
    }

    protected function validateUrl(mixed $url, ExecutionContextInterface $context): void
    {
        if (is_string($url) && $url !== '' && !in_array(parse_url($url, PHP_URL_HOST), $this->embedHosts, true)) {
            $context->addViolation('This host is not allowed for embedding.');
        }
    }
}
