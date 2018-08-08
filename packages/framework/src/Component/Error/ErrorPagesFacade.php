<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use AppKernel;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ErrorPagesFacade
{
    const PAGE_STATUS_CODE_404 = Response::HTTP_NOT_FOUND;
    const PAGE_STATUS_CODE_500 = Response::HTTP_INTERNAL_SERVER_ERROR;

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
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;
    
    public function __construct(
        string $errorPagesDir,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        Filesystem $filesystem
    ) {
        $this->errorPagesDir = $errorPagesDir;
        $this->domain = $domain;
        $this->domainRouterFactory = $domainRouterFactory;
        $this->filesystem = $filesystem;
    }

    public function generateAllErrorPagesForProduction(): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $this->generateAndSaveErrorPage($domainConfig->getId(), self::PAGE_STATUS_CODE_404);
            $this->generateAndSaveErrorPage($domainConfig->getId(), self::PAGE_STATUS_CODE_500);
        }
    }
    
    public function getErrorPageContentByDomainIdAndStatusCode(int $domainId, int $statusCode): string
    {
        $errorPageContent = file_get_contents($this->getErrorPageFilename($domainId, $statusCode));
        if ($errorPageContent === false) {
            throw new \Shopsys\FrameworkBundle\Component\Error\Exception\ErrorPageNotFoundException($domainId, $statusCode);
        }

        return $errorPageContent;
    }
    
    public function getErrorPageStatusCodeByStatusCode(int $statusCode): int
    {
        if ($statusCode === Response::HTTP_NOT_FOUND || $statusCode === Response::HTTP_FORBIDDEN) {
            return self::PAGE_STATUS_CODE_404;
        } else {
            return self::PAGE_STATUS_CODE_500;
        }
    }
    
    protected function generateAndSaveErrorPage(int $domainId, int $statusCode): void
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

        $this->filesystem->dumpFile(
            $this->getErrorPageFilename($domainId, $statusCode),
            $errorPageContent
        );
    }
    
    protected function getErrorPageFilename(int $domainId, int $statusCode): string
    {
        return $this->errorPagesDir . $statusCode . '_ ' . $domainId . '.html';
    }
    
    protected function getUrlContent(string $errorPageUrl, int $expectedStatusCode): string
    {
        $errorPageKernel = new AppKernel(EnvironmentType::PRODUCTION, false);

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
