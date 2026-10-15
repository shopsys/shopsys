<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Mail;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TextField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\VariableField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Profile\ContentProfileEnum;

/**
 * Image whose address is a placeholder of the mail template (e.g. a product image), filled in when the mail is sent
 */
#[ContentBlock(type: 'mail-image-variable', profiles: [ContentProfileEnum::MAIL])]
class MailImageVariableBlock extends AbstractContentBlock
{
    #[Override]
    public function getLabel(): string
    {
        return t('Image from variable');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:photo';
    }

    // the offered placeholders depend on the mail template, so the form of the editor passes them in
    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(VariableField::class)::create('variable')->label(t('Variable'))->required(),
            ExtendedClassNameResolver::resolve(TextField::class)::create('alt')->label(t('Alternative text')),
        ];
    }
}
