<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Twig_Environment;
use Twig_Template;

class FeedXmlWriter
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $feedTemplatePath
     * @param string $targetFilepath
     */
    public function writeBegin(DomainConfig $domainConfig, $feedTemplatePath, $targetFilepath)
    {
        $twigTemplate = $this->twig->loadTemplate($feedTemplatePath);
        $renderedBlock = $this->getRenderedBlock($twigTemplate, 'begin', ['domainConfig' => $domainConfig]);
        file_put_contents($targetFilepath, $renderedBlock);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $feedTemplatePath
     * @param string $targetFilepath
     */
    public function writeEnd(DomainConfig $domainConfig, $feedTemplatePath, $targetFilepath)
    {
        $twigTemplate = $this->twig->loadTemplate($feedTemplatePath);
        $renderedBlock = $this->getRenderedBlock($twigTemplate, 'end', ['domainConfig' => $domainConfig]);
        file_put_contents($targetFilepath, $renderedBlock, FILE_APPEND);
    }

    /**
     * @param \Shopsys\ProductFeed\FeedItemInterface[] $items
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param string $feedTemplatePath
     * @param string $targetFilepath
     */
    public function writeItems(array $items, DomainConfig $domainConfig, $feedTemplatePath, $targetFilepath)
    {
        $twigTemplate = $this->twig->loadTemplate($feedTemplatePath);

        $renderedContent = '';
        foreach ($items as $item) {
            $renderedContent .= $this->getRenderedBlock(
                $twigTemplate,
                'item',
                [
                    'item' => $item,
                    'domainConfig' => $domainConfig,
                ]
            );
        }

        file_put_contents($targetFilepath, $renderedContent, FILE_APPEND);
    }

    /**
     * @param \Twig_Template $twigTemplate
     * @param string $name
     * @param array $parameters
     * @return string
     */
    private function getRenderedBlock(Twig_Template $twigTemplate, $name, array $parameters = [])
    {
        if ($twigTemplate->hasBlock($name)) {
            $templateParameters = array_merge(
                $this->twig->getGlobals(),
                $parameters
            );
            return $twigTemplate->renderBlock($name, $templateParameters);
        }

        throw new \Shopsys\FrameworkBundle\Model\Feed\Exception\TemplateBlockNotFoundException($name, $twigTemplate->getTemplateName());
    }
}
