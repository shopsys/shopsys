<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use FOS\CKEditorBundle\Config\CKEditorConfigurationInterface;
use FOS\CKEditorBundle\Twig\CKEditorExtension as BaseCKEditorExtension;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CKEditorExtension extends AbstractExtension
{
    public function __construct(
        protected readonly CKEditorConfigurationInterface $configuration,
        protected readonly BaseCKEditorExtension $ckEditorExtension,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ckeditor_init', $this->ckEditorInit(...), ['is_safe' => ['html']]),
        ];
    }

    public function ckEditorInit(): string
    {
        return sprintf(
            '<script type="text/javascript">
                var CKEDITOR_BASEPATH = "%s";
            </script>
            <script type="text/javascript" src="%s"></script>',
            $this->ckEditorExtension->renderBasePath($this->configuration->getBasePath()),
            $this->ckEditorExtension->renderJsPath($this->configuration->getJsPath()),
        );
    }
}
