<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GrapesJsMailExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateBuilder $mailTemplateBuilder
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        protected readonly MailTemplateBuilder $mailTemplateBuilder,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getMailTemplate', $this->getMailTemplate(...), ['is_safe' => ['html']]),
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
