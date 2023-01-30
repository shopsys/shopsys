<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Mail\MailTemplateBuilder;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GrapesJsMailExtension extends AbstractExtension
{
    /**
     * @param \App\Model\Mail\MailTemplateBuilder $mailTemplateBuilder
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        private readonly MailTemplateBuilder $mailTemplateBuilder,
        private readonly AdminDomainTabsFacade $adminDomainTabsFacade
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getMailTemplate', [$this, 'getMailTemplate'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string|null $content
     * @return string
     */
    public function getMailTemplate(?string $content): string
    {
        return $this->mailTemplateBuilder->getMailTemplateWithContent($this->adminDomainTabsFacade->getSelectedDomainId(), $content);
    }
}
