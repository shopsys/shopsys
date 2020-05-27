<?php

declare(strict_types=1);

namespace App\Component\Error;

use App\Component\Domain\Domain;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider;
use Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade as BaseErrorPagesFacade;
use Shopsys\FrameworkBundle\Component\Error\Exception\ErrorPageNotFoundException;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @property \App\Component\Domain\Domain $domain
 */
class ErrorPagesFacade extends BaseErrorPagesFacade
{
    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $abstractFilesystem;

    /**
     * @param string $errorPagesDir
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \League\Flysystem\FilesystemInterface $abstractFilesystem
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider|null $errorIdProvider
     */
    public function __construct(
        $errorPagesDir,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        Filesystem $filesystem,
        FilesystemInterface $abstractFilesystem,
        ?ErrorIdProvider $errorIdProvider = null
    ) {
        $this->errorPagesDir = $errorPagesDir;
        $this->domain = $domain;
        $this->domainRouterFactory = $domainRouterFactory;
        $this->filesystem = $filesystem;
        $this->abstractFilesystem = $abstractFilesystem;
        $this->errorIdProvider = $errorIdProvider;

        parent::__construct($errorPagesDir, $domain, $domainRouterFactory, $filesystem, $errorIdProvider);
    }

    /**
     * @param int $domainId
     * @param int $statusCode
     * @return string
     */
    public function getErrorPageContentByDomainIdAndStatusCode($domainId, $statusCode)
    {
        $errorPageContent = $this->abstractFilesystem->read($this->getErrorPageFilename($domainId, $statusCode));
        if ($errorPageContent === false) {
            throw new ErrorPageNotFoundException($domainId, $statusCode);
        }

        $errorPageContent = str_replace('{{ERROR_ID}}', $this->errorIdProvider->getErrorId(), $errorPageContent);

        return $errorPageContent;
    }

    /**
     * @param int $domainId
     * @param int $statusCode
     */
    protected function generateAndSaveErrorPage($domainId, $statusCode)
    {
        $domainRouter = $this->domainRouterFactory->getRouter($domainId);
        $errorPageUrl = $domainRouter->generate(
            'front_error_page_format',
            [
                '_format' => 'html',
                'code' => $statusCode,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $errorPageContent = $this->getUrlContent($errorPageUrl, $statusCode);

        $filesystemConfig = [
            'visibility' => AdapterInterface::VISIBILITY_PRIVATE,
        ];

        if ($this->abstractFilesystem->has($this->getErrorPageFilename($domainId, $statusCode))) {
            $this->abstractFilesystem->update(
                $this->getErrorPageFilename($domainId, $statusCode),
                $errorPageContent,
                $filesystemConfig
            );
        } else {
            $this->abstractFilesystem->put(
                $this->getErrorPageFilename($domainId, $statusCode),
                $errorPageContent,
                $filesystemConfig
            );
        }
    }
}
