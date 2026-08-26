<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\Exception\TemplateBlockNotFoundException;
use Twig\TemplateWrapper;

class FeedRenderer
{
    public function __construct(protected readonly TemplateWrapper $template)
    {
    }

    public function renderBegin(DomainConfig $domainConfig): string
    {
        return $this->getRenderedBlock('begin', ['domainConfig' => $domainConfig]);
    }

    public function renderEnd(DomainConfig $domainConfig): string
    {
        return $this->getRenderedBlock('end', ['domainConfig' => $domainConfig]);
    }

    public function renderItem(DomainConfig $domainConfig, FeedItemInterface $item): string
    {
        return $this->getRenderedBlock('item', ['item' => $item, 'domainConfig' => $domainConfig]);
    }

    protected function getRenderedBlock(string $name, array $parameters): string
    {
        if (!$this->template->hasBlock($name)) {
            throw new TemplateBlockNotFoundException($name, $this->template->getTemplateName());
        }

        return $this->template->renderBlock($name, $parameters);
    }
}
