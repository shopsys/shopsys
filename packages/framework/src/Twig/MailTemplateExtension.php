<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MailTemplateExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder $mailTemplateBuilder
     */
    public function __construct(
        protected readonly MailTemplateBuilder $mailTemplateBuilder,
    ) {
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('mailImageSrc', $this->getMailImageSrc(...)),
        ];
    }

    /**
     * @param int $domainId
     * @param string $imageNameWithExtension
     * @return string
     */
    public function getMailImageSrc(int $domainId, string $imageNameWithExtension): string
    {
        return $this->mailTemplateBuilder->getMailImageSrc($domainId, $imageNameWithExtension);
    }
}
