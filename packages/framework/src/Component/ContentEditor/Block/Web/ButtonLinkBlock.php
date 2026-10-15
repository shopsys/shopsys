<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\CheckboxField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;

#[ContentBlock(type: 'button-link')]
class ButtonLinkBlock extends AbstractContentBlock
{
    public const string POSITION_LEFT = 'left';
    public const string POSITION_CENTER = 'center';
    public const string POSITION_RIGHT = 'right';

    public const string VARIANT_PRIMARY = 'primary';
    public const string VARIANT_SECONDARY = 'secondary';
    public const string VARIANT_TERTIARY = 'tertiary';

    #[Override]
    public function getLabel(): string
    {
        return t('Button');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:click';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(TextField::class)::create('text')->label(t('Button text'))->required(),
            ExtendedClassNameResolver::resolve(UrlField::class)::create('url')->label(t('Link'))->required(),
            ExtendedClassNameResolver::resolve(CheckboxField::class)::create('openInNewTab')->label(t('Open in new tab')),
            ExtendedClassNameResolver::resolve(ChoiceField::class)::create('position', [static::POSITION_LEFT, static::POSITION_CENTER, static::POSITION_RIGHT])
                ->label(t('Position'))
                ->default(static::POSITION_CENTER),
            ExtendedClassNameResolver::resolve(ChoiceField::class)::create('variant', [static::VARIANT_PRIMARY, static::VARIANT_SECONDARY, static::VARIANT_TERTIARY])
                ->label(t('Style'))
                ->default(static::VARIANT_PRIMARY),
        ];
    }
}
