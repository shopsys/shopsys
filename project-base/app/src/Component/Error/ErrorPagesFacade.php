<?php

declare(strict_types=1);

namespace App\Component\Error;

use App\Kernel;
use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider;
use Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade as BaseErrorPagesFacade;
use Shopsys\FrameworkBundle\Component\Error\Exception\BadErrorPageStatusCodeException;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\HttpFoundation\Request;

class ErrorPagesFacade extends BaseErrorPagesFacade
{
    /**
     * @param string $errorPagesDir
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorIdProvider $errorIdProvider
     * @param \League\Flysystem\FilesystemOperator $mainFilesystem
     * @param string $environment
     */
    public function __construct(
        string $errorPagesDir,
        Domain $domain,
        DomainRouterFactory $domainRouterFactory,
        ErrorIdProvider $errorIdProvider,
        FilesystemOperator $mainFilesystem,
        private readonly string $environment = EnvironmentType::PRODUCTION,
    ) {
        parent::__construct($errorPagesDir, $domain, $domainRouterFactory, $errorIdProvider, $mainFilesystem);
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
        $errorPageKernel->shutdown();

        if ($expectedStatusCode !== $errorPageResponse->getStatusCode()) {
            throw new BadErrorPageStatusCodeException(
                $errorPageUrl,
                $expectedStatusCode,
                $errorPageResponse->getStatusCode()
            );
        }

        return $errorPageResponse->getContent();
    }
}
