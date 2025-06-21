<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CKEditor;

use FOS\CKEditorBundle\Renderer\CKEditorRendererInterface;
use Override;

class CKEditorRendererDecorator implements CKEditorRendererInterface
{
    /**
     * @param \FOS\CKEditorBundle\Renderer\CKEditorRendererInterface $baseCkEditorRenderer
     */
    public function __construct(
        protected readonly CKEditorRendererInterface $baseCkEditorRenderer,
    ) {
    }

    /**
     * @param string $basePath
     * @return string
     */
    #[Override]
    public function renderBasePath(string $basePath): string
    {
        return $this->baseCkEditorRenderer->renderBasePath($basePath);
    }

    /**
     * @param string $jsPath
     * @return string
     */
    #[Override]
    public function renderJsPath(string $jsPath): string
    {
        return $this->baseCkEditorRenderer->renderJsPath($jsPath);
    }

    /**
     * @param string $id
     * @param array $config
     * @param array $options
     * @return string
     */
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

    /**
     * @param string $id
     * @return string
     */
    #[Override]
    public function renderDestroy(string $id): string
    {
        return $this->baseCkEditorRenderer->renderDestroy($id);
    }

    /**
     * @param string $name
     * @param array $plugin
     * @return string
     */
    #[Override]
    public function renderPlugin(string $name, array $plugin): string
    {
        return $this->baseCkEditorRenderer->renderPlugin($name, $plugin);
    }

    /**
     * @param string $name
     * @param array $stylesSet
     * @return string
     */
    #[Override]
    public function renderStylesSet(string $name, array $stylesSet): string
    {
        return $this->baseCkEditorRenderer->renderStylesSet($name, $stylesSet);
    }

    /**
     * @param string $name
     * @param array $template
     * @return string
     */
    #[Override]
    public function renderTemplate(string $name, array $template): string
    {
        return $this->baseCkEditorRenderer->renderTemplate($name, $template);
    }
}
