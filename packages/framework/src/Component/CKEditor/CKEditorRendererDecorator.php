<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CKEditor;

use FOS\CKEditorBundle\Renderer\CKEditorRendererInterface;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class CKEditorRendererDecorator implements CKEditorRendererInterface
{
    /**
     * @param \FOS\CKEditorBundle\Renderer\CKEditorRendererInterface $baseCkEditorRenderer
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly CKEditorRendererInterface $baseCkEditorRenderer,
        protected readonly Localization $localization,
    ) {
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
     * @param array $config
     * @param array $options
     * @return string
     */
    public function renderWidget(string $id, array $config, array $options = []): string
    {
        $config['language'] = $this->getRequestLanguage();

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
     * @param array $plugin
     * @return string
     */
    public function renderPlugin(string $name, array $plugin): string
    {
        return $this->baseCkEditorRenderer->renderPlugin($name, $plugin);
    }

    /**
     * @param string $name
     * @param array $stylesSet
     * @return string
     */
    public function renderStylesSet(string $name, array $stylesSet): string
    {
        return $this->baseCkEditorRenderer->renderStylesSet($name, $stylesSet);
    }

    /**
     * @param string $name
     * @param array $template
     * @return string
     */
    public function renderTemplate(string $name, array $template): string
    {
        return $this->baseCkEditorRenderer->renderTemplate($name, $template);
    }

    /**
     * @return string
     */
    protected function getRequestLanguage(): string
    {
        return $this->localization->getAdminLocale();
    }
}
