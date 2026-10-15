<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Mail;

use Shopsys\FrameworkBundle\Component\ContentEditor\Document\ContentDocument;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder;

class MailPreviewProvider
{
    public function __construct(
        protected readonly MailContentRenderer $mailContentRenderer,
        protected readonly MailTemplateBuilder $mailTemplateBuilder,
    ) {
    }

    /**
     * The mail as it is sent, only the placeholders are replaced with their labels instead of real values
     *
     * @param array<int, array{label: string, placeholder: string, isRequired: bool, isImage: bool}> $variables
     */
    public function getPreviewHtml(ContentDocument $document, int $domainId, array $variables): string
    {
        $html = $this->mailTemplateBuilder->getMailTemplateWithContent(
            $domainId,
            $this->mailContentRenderer->render($document, $domainId),
        );

        $labelsByPlaceholder = [];

        foreach ($variables as $variable) {
            $labelsByPlaceholder[$variable['placeholder']] = $variable['label'];
        }

        return strtr($html, $labelsByPlaceholder);
    }
}
