<?php

declare(strict_types=1);

namespace App\Component\Error;

use App\Kernel;
use DevOps\KubernetesDeployment\Component\Error\ErrorPagesFacade as BaseErrorPagesFacade;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider;
use Shopsys\FrameworkBundle\Component\Error\Exception\BadErrorPageStatusCodeException;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ErrorPagesFacade extends BaseErrorPagesFacade
{
    private string $environment;

    /**
     * @param string $errorPagesDir
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \League\Flysystem\FilesystemInterface $abstractFilesystem
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider $errorIdProvider
     * @param string $environment
     */
    public function __construct(
        $errorPagesDir,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        Filesystem $filesystem,
        FilesystemInterface $abstractFilesystem,
        ErrorIdProvider $errorIdProvider,
        string $environment = EnvironmentType::PRODUCTION
    ) {
        parent::__construct($errorPagesDir, $domain, $domainRouterFactory, $filesystem, $abstractFilesystem, $errorIdProvider);

        $this->environment = $environment;
    }

    /**
     * @param int $domainId
     * @param int $statusCode
     * @return string
     */
    protected function getErrorPageFilename($domainId, $statusCode): string
    {
        return $this->errorPagesDir . $this->environment . '/' . $statusCode . '_' . $domainId . '.html';
    }

    /**
     * @param string $errorPageUrl
     * @param int $expectedStatusCode
     * @return string
     */
    protected function getUrlContent($errorPageUrl, $expectedStatusCode): string
    {
        $errorPageKernel = new Kernel($this->environment, false);

        $errorPageFakeRequest = Request::create($errorPageUrl);

        $errorPageResponse = $errorPageKernel->handle($errorPageFakeRequest);
        $errorPageKernel->terminate($errorPageFakeRequest, $errorPageResponse);

        if ($expectedStatusCode !== $errorPageResponse->getStatusCode()) {
            throw new BadErrorPageStatusCodeException(
                $errorPageUrl,
                $expectedStatusCode,
                $errorPageResponse->getStatusCode()
            );
        }

        return $errorPageResponse->getContent();
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
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->getUrlContent($errorPageUrl, $statusCode);
    }
}
