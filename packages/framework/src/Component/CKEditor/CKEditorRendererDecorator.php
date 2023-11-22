<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CKEditor;

use FOS\CKEditorBundle\Renderer\CKEditorRendererInterface;

class CKEditorRendererDecorator implements CKEditorRendererInterface
{
    /**
     * @param \FOS\CKEditorBundle\Renderer\CKEditorRendererInterface $baseCkEditorRenderer
     */
    public function __construct(protected readonly CKEditorRendererInterface $baseCkEditorRenderer)
    {
    }

    /**
     * @param string $basePath
     * @return string
     */
    public function renderBasePath(string $basePath): string
    {
        return $this->baseCkEditorRenderer->renderBasePath($basePath);
    }

    /**
     * @param string $jsPath
     * @return string
     */
    public function renderJsPath(string $jsPath): string
    {
        return $this->baseCkEditorRenderer->renderJsPath($jsPath);
    }

    /**
     * @param string $id
     * @param mixed[] $config
     * @param mixed[] $options
     * @return string
     */
    public function renderWidget(string $id, array $config, array $options = []): string
    {
        return sprintf(
            '$("#%s-preview").click(function() {
                %s
                %s
            });',
            $id,
            $this->baseCkEditorRenderer->renderWidget($id, $config, $options),
            $this->renderJsValidation($id),
        );
    }

    /**
     * @param string $id
     * @return string
     */
    protected function renderJsValidation(string $id): string
    {
        return sprintf(
            'CKEDITOR.instances["%1$s"].on("change", function () {
                $("#%1$s").jsFormValidator("validate");
            });',
            $id,
        );
    }

    /**
     * @param string $id
     * @return string
     */
    public function renderDestroy(string $id): string
    {
        return $this->baseCkEditorRenderer->renderDestroy($id);
    }

    /**
     * @param string $name
     * @param mixed[] $plugin
     * @return string
     */
    public function renderPlugin(string $name, array $plugin): string
    {
        return $this->baseCkEditorRenderer->renderPlugin($name, $plugin);
    }

    /**
     * @param string $name
     * @param mixed[] $stylesSet
     * @return string
     */
    public function renderStylesSet(string $name, array $stylesSet): string
    {
        return $this->baseCkEditorRenderer->renderStylesSet($name, $stylesSet);
    }

    /**
     * @param string $name
     * @param mixed[] $template
     * @return string
     */
    public function renderTemplate(string $name, array $template): string
    {
        return $this->baseCkEditorRenderer->renderTemplate($name, $template);
    }
}
