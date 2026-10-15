<?php

declare(strict_types=1);

namespace App\ContentEditor\Block;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;

/**
 * A block that exists only in the project, an example of adding a block, see docs/extensibility/content-editor-blocks.md
 */
#[ContentBlock(type: 'faq')]
final class FaqBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return t('FAQ item');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:help';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(TextField::class)::create('question')->label(t('Question'))->required(),
            ExtendedClassNameResolver::resolve(WysiwygField::class)::create('answer')->label(t('Answer'))->required(),
        ];
    }
}
