<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ColorField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

#[ContentBlock(type: 'mail-button', profiles: [ContentProfileEnum::MAIL])]
class MailButtonBlock extends AbstractContentBlock
{
    public const string POSITION_LEFT = 'left';
    public const string POSITION_CENTER = 'center';
    public const string POSITION_RIGHT = 'right';

    public const string DEFAULT_COLOR = '#00C8B7';

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
            ExtendedClassNameResolver::resolve(UrlField::class)::create('url')->label(t('Link'))->required()->allowVariables(),
            ExtendedClassNameResolver::resolve(ChoiceField::class)::create('position', [static::POSITION_LEFT, static::POSITION_CENTER, static::POSITION_RIGHT])
                ->label(t('Position'))
                ->default(static::POSITION_CENTER),
            ExtendedClassNameResolver::resolve(ColorField::class)::create('color')->label(t('Color'))->default(static::DEFAULT_COLOR),
        ];
    }
}
