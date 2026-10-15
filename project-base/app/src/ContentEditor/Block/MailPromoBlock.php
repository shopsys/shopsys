<?php

declare(strict_types=1);

namespace App\ContentEditor\Block;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\UrlField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\WysiwygField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

/**
 * A mail block that exists only in the project, rendered by templates/bundles/ShopsysFrameworkBundle/ContentEditor/Mail/mail-promo.html.twig
 */
#[ContentBlock(type: 'mail-promo', profiles: [ContentProfileEnum::MAIL])]
final class MailPromoBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return t('Promo');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:speakerphone';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(TextField::class)::create('headline')->label(t('Headline'))->required(),
            ExtendedClassNameResolver::resolve(WysiwygField::class)::create('text')->label(t('Text'))->required(),
            ExtendedClassNameResolver::resolve(UrlField::class)::create('url')->label(t('Link'))->allowVariables(),
        ];
    }
}
