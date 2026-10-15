<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;

#[ContentBlock(type: 'text')]
class TextBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return t('Text');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:text-size';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(WysiwygField::class)::create('html')->label(t('Text'))->required(),
        ];
    }
}
