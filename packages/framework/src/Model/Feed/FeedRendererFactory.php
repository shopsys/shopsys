<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Twig\Environment;

class FeedRendererFactory
{
    public function __construct(protected readonly Environment $twig)
    {
    }

    public function create(FeedInterface $feed): FeedRenderer
    {
        $templateFilepath = $feed->getTemplateFilepath();
        $twigTemplate = $this->twig->load($templateFilepath);

        return new FeedRenderer($this->twig, $twigTemplate);
    }
}
