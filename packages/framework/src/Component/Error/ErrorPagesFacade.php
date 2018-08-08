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

    /**
     * @param string $errorPagesDir
     */
    public function __construct(
        $errorPagesDir,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        Filesystem $filesystem
    ) {
        $this->errorPagesDir = $errorPagesDir;
        $this->domain = $domain;
        $this->domainRouterFactory = $domainRouterFactory;
        $this->filesystem = $filesystem;
    }

    public function generateAllErrorPagesForProduction()
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $this->generateAndSaveErrorPage($domainConfig->getId(), self::PAGE_STATUS_CODE_404);
            $this->generateAndSaveErrorPage($domainConfig->getId(), self::PAGE_STATUS_CODE_500);
        }
    }

    /**
     * @param int $domainId
     * @param int $statusCode
     * @return string
     */
    public function getErrorPageContentByDomainIdAndStatusCode($domainId, $statusCode)
    {
        $errorPageContent = file_get_contents($this->getErrorPageFilename($domainId, $statusCode));
        if ($errorPageContent === false) {
            throw new \Shopsys\FrameworkBundle\Component\Error\Exception\ErrorPageNotFoundException($domainId, $statusCode);
        }

        return $errorPageContent;
    }

    /**
     * @param int $statusCode
     * @return int
     */
    public function getErrorPageStatusCodeByStatusCode($statusCode)
    {
        if ($statusCode === Response::HTTP_NOT_FOUND || $statusCode === Response::HTTP_FORBIDDEN) {
            return self::PAGE_STATUS_CODE_404;
        } else {
            return self::PAGE_STATUS_CODE_500;
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

        $this->filesystem->dumpFile(
            $this->getErrorPageFilename($domainId, $statusCode),
            $errorPageContent
        );
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
