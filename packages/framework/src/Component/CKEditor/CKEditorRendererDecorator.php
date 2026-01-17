<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CKEditor;

use FOS\CKEditorBundle\Renderer\CKEditorRendererInterface;
use Override;

class CKEditorRendererDecorator implements CKEditorRendererInterface
{
    public function __construct(
        protected readonly CKEditorRendererInterface $baseCkEditorRenderer,
    ) {
    }

    #[Override]
    public function renderBasePath(string $basePath): string
    {
        return $this->baseCkEditorRenderer->renderBasePath($basePath);
    }

    #[Override]
    public function renderJsPath(string $jsPath): string
    {
        return $this->baseCkEditorRenderer->renderJsPath($jsPath);
    }

    #[Override]
    public function renderWidget(string $id, array $config, array $options = []): string
    {
        return sprintf(
            '$("#%s-preview").click(function() {
                %s
            });',
            $id,
            $this->baseCkEditorRenderer->renderWidget($id, $config, $options),
        );
    }

    #[Override]
    public function renderDestroy(string $id): string
    {
        return $this->baseCkEditorRenderer->renderDestroy($id);
    }

    #[Override]
    public function renderPlugin(string $name, array $plugin): string
    {
        return $this->baseCkEditorRenderer->renderPlugin($name, $plugin);
    }

    #[Override]
    public function renderStylesSet(string $name, array $stylesSet): string
    {
        return $this->baseCkEditorRenderer->renderStylesSet($name, $stylesSet);
    }

    #[Override]
    public function renderTemplate(string $name, array $template): string
    {
        return $this->baseCkEditorRenderer->renderTemplate($name, $template);
    }
}
