<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Multidomain\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\NoDomainSelectedException;
use Shopsys\FrameworkBundle\Component\Domain\Multidomain\Twig\Exception\MissingDependencyException;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader as BaseFilesystemLoader;

class FilesystemLoader extends BaseFilesystemLoader
{
    protected ?Domain $domain = null;

    /**
     * @param array $paths
     * @param string|null $rootPath
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain|null $domain
     */
    public function __construct(
        array $paths = [],
        ?string $rootPath = null,
        ?Domain $domain = null
    ) {
        parent::__construct($paths, $rootPath);

        $this->domain = $domain;
        $this->assertDomainDependency();
    }

    /**
     * When exists a template with filename.{designId}.html.twig, then it is automatically used
     * on domain with this {designId} whenever template named filename.html.twig is on input
     *
     * {@inheritdoc}
     */
    protected function findTemplate($template, $throw = true): string|false|null
    {
        $templateName = (string)$template;
        $multidesignTemplate = null;

        if (strpos($templateName, 'Front/') === 0) {
            $multidesignTemplate = $this->findMultidesignTemplate($templateName);
        }

        if ($multidesignTemplate !== null) {
            return $multidesignTemplate;
        }

        return parent::findTemplate($templateName);
    }

    protected function assertDomainDependency()
    {
        if (!($this->domain instanceof Domain)) {
            $message = sprintf('Template loader needs an instance of %s class', Domain::class);

            throw new MissingDependencyException($message);
        }
    }

    /**
     * @param string $templateName
     * @return string|null
     */
    protected function findMultidesignTemplate($templateName)
    {
        try {
            $designId = $this->domain->getDesignId();

            if ($designId !== null) {
                $multidesignTemplateName = preg_replace(
                    '/^(.*)(\.[^\.]*\.twig)$/',
                    '$1.' . $designId . '$2',
                    $templateName
                );

                try {
                    return parent::findTemplate($multidesignTemplateName);
                } catch (LoaderError $loaderException) {
                    if (strpos($loaderException->getMessage(), 'Unable to find template') !== 0) {
                        $message = sprintf(
                            'Unexpected exception when trying to load multidesign template `%s`',
                            $multidesignTemplateName
                        );

                        throw new LoaderError($message, -1, null, $loaderException);
                    }
                }
            }
        } catch (NoDomainSelectedException $ex) {
            return null;
        }

        return null;
    }
}
