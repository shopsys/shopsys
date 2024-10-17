<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use App\Kernel;
use Exception;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Error\Exception\BadErrorPageStatusCodeException;
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
     * @param string $errorPagesDir
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider $errorIdProvider
     * @param \League\Flysystem\FilesystemOperator $mainFilesystem
     * @param string $environment
     */
    public function __construct(
        protected readonly string $errorPagesDir,
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly ErrorIdProvider $errorIdProvider,
        protected readonly FilesystemOperator $mainFilesystem,
        protected readonly string $environment = EnvironmentType::PRODUCTION,
    ) {
    }

    public function generateAllErrorPagesForProduction(): void
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
    public function getErrorPageContentByDomainIdAndStatusCode(int $domainId, int $statusCode): string
    {
        try {
            $errorPageContent = $this->mainFilesystem->read($this->getErrorPageFilename($domainId, $statusCode));
        } catch (Exception) {
            $errorPageContent = $this->generateErrorPage($domainId, $statusCode);
        }

        return str_replace('{{ERROR_ID}}', $this->errorIdProvider->getErrorId(), $errorPageContent);
    }

    /**
     * @param int $statusCode
     * @return int
     */
    public function getErrorPageStatusCodeByStatusCode(int $statusCode): int
    {
        switch ($statusCode) {
            case Response::HTTP_NOT_FOUND:
            case Response::HTTP_FORBIDDEN:
            case Response::HTTP_METHOD_NOT_ALLOWED:
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
    protected function generateAndSaveErrorPage(int $domainId, int $statusCode): void
    {
        $errorPageContent = $this->generateErrorPage($domainId, $statusCode);

        $filesystemConfig = [
            'visibility' => Visibility::PRIVATE,
        ];

        $this->mainFilesystem->write(
            $this->getErrorPageFilename($domainId, $statusCode),
            $errorPageContent,
            $filesystemConfig,
        );
    }

    /**
     * @param int $domainId
     * @param int $statusCode
     * @return string
     */
    protected function generateErrorPage(int $domainId, int $statusCode): string
    {
        $domainRouter = $this->domainRouterFactory->getRouter($domainId);
        $errorPageUrl = $domainRouter->generate(
            'admin_error_page_format',
            [
                '_format' => 'html',
                'code' => $statusCode,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return $this->getUrlContent($errorPageUrl, $statusCode);
    }

    /**
     * @param int $domainId
     * @param int $statusCode
     * @return string
     */
    protected function getErrorPageFilename(int $domainId, int $statusCode): string
    {
        return $this->errorPagesDir . $this->environment . '/' . $statusCode . '_' . $domainId . '.html';
    }

    /**
     * @param string $errorPageUrl
     * @param int $expectedStatusCode
     * @return string
     */
    protected function getUrlContent(string $errorPageUrl, int $expectedStatusCode): string
    {
        $errorPageKernel = new Kernel($this->environment, false);

        $errorPageFakeRequest = Request::create($errorPageUrl);

        $errorPageResponse = $errorPageKernel->handle($errorPageFakeRequest);
        $errorPageKernel->terminate($errorPageFakeRequest, $errorPageResponse);
        $errorPageKernel->shutdown();

        if ($expectedStatusCode !== $errorPageResponse->getStatusCode()) {
            throw new BadErrorPageStatusCodeException(
                $errorPageUrl,
                $expectedStatusCode,
                $errorPageResponse->getStatusCode(),
            );
        }

        return $errorPageResponse->getContent();
    }
}
