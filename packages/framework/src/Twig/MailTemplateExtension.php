<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MailTemplateExtension extends AbstractExtension
{
    public function __construct(
        protected readonly MailTemplateBuilder $mailTemplateBuilder,
    ) {
    }

    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('mailImageSrc', $this->getMailImageSrc(...)),
        ];
    }

    public function getMailImageSrc(int $domainId, string $imageNameWithExtension): string
    {
        return $this->mailTemplateBuilder->getMailImageSrc($domainId, $imageNameWithExtension);
    }
}
