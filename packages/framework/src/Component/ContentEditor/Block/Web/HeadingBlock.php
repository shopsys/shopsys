<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;

#[ContentBlock(type: 'heading')]
class HeadingBlock extends AbstractContentBlock
{
    public const string LEVEL_H2 = 'h2';
    public const string LEVEL_H3 = 'h3';
    public const string LEVEL_H4 = 'h4';

    #[Override]
    public function getLabel(): string
    {
        return t('Heading');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:heading';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(TextField::class)::create('text')->label(t('Heading'))->required(),
            ExtendedClassNameResolver::resolve(ChoiceField::class)::create('level', [static::LEVEL_H2, static::LEVEL_H3, static::LEVEL_H4])
                ->label(t('Level'))
                ->default(static::LEVEL_H2),
        ];
    }
}
