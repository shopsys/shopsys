<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use FOS\CKEditorBundle\Config\CKEditorConfigurationInterface;
use FOS\CKEditorBundle\Twig\CKEditorExtension as BaseCKEditorExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CKEditorExtension extends AbstractExtension
{
    /**
     * @param \FOS\CKEditorBundle\Config\CKEditorConfigurationInterface $configuration
     * @param \FOS\CKEditorBundle\Twig\CKEditorExtension $ckEditorExtension
     */
    public function __construct(
        protected readonly CKEditorConfigurationInterface $configuration,
        protected readonly BaseCKEditorExtension $ckEditorExtension
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ckeditor_init', [$this, 'ckEditorInit'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return string
     */
    public function ckEditorInit(): string
    {
        return sprintf(
            '<script type="text/javascript">
                var CKEDITOR_BASEPATH = "%s";
            </script>
            <script type="text/javascript" src="%s"></script>',
            $this->ckEditorExtension->renderBasePath($this->configuration->getBasePath()),
            $this->ckEditorExtension->renderJsPath($this->configuration->getJsPath())
        );
    }
}
