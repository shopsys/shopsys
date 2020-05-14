<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use App\Kernel;
use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ErrorPagesFacade
{
    protected const PAGE_STATUS_CODE_404 = Response::HTTP_NOT_FOUND;
    protected const PAGE_STATUS_CODE_410 = Response::HTTP_GONE;
    protected const PAGE_STATUS_CODE_500 = Response::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * @var string
     */
    protected $errorPagesDir;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory
     */
    protected $domainRouterFactory;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider
     */
    protected $errorIdProvider;

    /**
     * @param string $errorPagesDir
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider $errorIdProvider
     */
    public function __construct(
        $errorPagesDir,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        FilesystemInterface $filesystem,
        ErrorIdProvider $errorIdProvider
    ) {
        $this->errorPagesDir = $errorPagesDir;
        $this->domain = $domain;
        $this->domainRouterFactory = $domainRouterFactory;
        $this->filesystem = $filesystem;
        $this->errorIdProvider = $errorIdProvider;
    }

    public function generateAllErrorPagesForProduction()
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $this->generateAndSaveErrorPage($domainConfig->getId(), static::PAGE_STATUS_CODE_404);
            $this->generateAndSaveErrorPage($domainConfig->getId(), static::PAGE_STATUS_CODE_410);
            $this->generateAndSaveErrorPage($domainConfig->getId(), static::PAGE_STATUS_CODE_500);
        }
    }

    /**
     * @param int $domainId
     * @param int $statusCode
     * @return string
     */
    public function getErrorPageContentByDomainIdAndStatusCode($domainId, $statusCode)
    {
        $errorPageContent = $this->filesystem->read($this->getErrorPageFilename($domainId, $statusCode));
        if ($errorPageContent === false) {
            throw new \Shopsys\FrameworkBundle\Component\Error\Exception\ErrorPageNotFoundException($domainId, $statusCode);
        }

        $errorPageContent = str_replace('{{ERROR_ID}}', $this->errorIdProvider->getErrorId(), $errorPageContent);

        return $errorPageContent;
    }

    /**
     * @param int $statusCode
     * @return int
     */
    public function getErrorPageStatusCodeByStatusCode($statusCode)
    {
        switch ($statusCode) {
            case Response::HTTP_NOT_FOUND:
            case Response::HTTP_FORBIDDEN:
                return static::PAGE_STATUS_CODE_404;
            case Response::HTTP_GONE:
                return static::PAGE_STATUS_CODE_410;
            default:
                return static::PAGE_STATUS_CODE_500;
        }
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

        if ($this->filesystem->has($this->getErrorPageFilename($domainId, $statusCode))) {
            $this->filesystem->update(
                $this->getErrorPageFilename($domainId, $statusCode),
                $errorPageContent,
                $filesystemConfig
            );
        } else {
            $this->filesystem->put(
                $this->getErrorPageFilename($domainId, $statusCode),
                $errorPageContent,
                $filesystemConfig
            );
        }
    }

    /**
     * @param int $domainId
     * @param int $statusCode
     * @return string
     */
    protected function getErrorPageFilename($domainId, $statusCode)
    {
        return $this->errorPagesDir . $statusCode . '_ ' . $domainId . '.html';
    }

    /**
     * @param string $errorPageUrl
     * @param int $expectedStatusCode
     * @return string
     */
    protected function getUrlContent($errorPageUrl, $expectedStatusCode)
    {
        $errorPageKernel = new Kernel(EnvironmentType::PRODUCTION, false);

        $errorPageFakeRequest = Request::create($errorPageUrl);

        $errorPageResponse = $errorPageKernel->handle($errorPageFakeRequest);
        $errorPageKernel->terminate($errorPageFakeRequest, $errorPageResponse);

        if ($expectedStatusCode !== $errorPageResponse->getStatusCode()) {
            throw new \Shopsys\FrameworkBundle\Component\Error\Exception\BadErrorPageStatusCodeException(
                $errorPageUrl,
                $expectedStatusCode,
                $errorPageResponse->getStatusCode()
            );
        }

        return $errorPageResponse->getContent();
    }
}
