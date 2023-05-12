<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\UnableToResolveDomainException;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Tracy\BlueScreen;
use Tracy\Debugger;

class ErrorController extends AbstractController
{
    /**
     * @param bool $debug
     * @param \Shopsys\FrameworkBundle\Component\Error\ExceptionListener $exceptionListener
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade $errorPagesFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param string $environment
     * @param string|null $overwriteDomainUrl
     */
    public function __construct(
        protected readonly bool $debug,
        protected readonly ExceptionListener $exceptionListener,
        protected readonly ErrorPagesFacade $errorPagesFacade,
        protected readonly Domain $domain,
        protected readonly string $environment,
        protected readonly ?string $overwriteDomainUrl = null
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function errorPagePreviewAction(Request $request, int $code): Response
    {
        return $this->renderTemplate($code, $request->getRequestFormat());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\ErrorHandler\Exception\FlattenException $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(
        Request $request,
        FlattenException $exception
    ): Response {
        if ($this->isUnableToResolveDomainInNotDebug($exception)) {
            return $this->createUnableToResolveDomainResponse($request);
        }

        if ($this->debug) {
            return $this->createDevelopmentResponse($request, $exception);
        }

        return $this->createProductionResponse($exception->getStatusCode());
    }

    /**
     * @param int $statusCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createProductionResponse(int $statusCode): Response
    {
        $errorPageStatusCode = $this->errorPagesFacade->getErrorPageStatusCodeByStatusCode($statusCode);
        $errorPageContent = $this->errorPagesFacade->getErrorPageContentByDomainIdAndStatusCode(
            $this->domain->getId(),
            $errorPageStatusCode
        );

        return new Response($errorPageContent, $errorPageStatusCode);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\ErrorHandler\Exception\FlattenException $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createDevelopmentResponse(Request $request, FlattenException $exception): Response
    {
        $lastThrowable = $this->exceptionListener->getLastThrowable();

        if ($lastThrowable !== null) {
            return $this->createTracyResponse($lastThrowable);
        }

        return $this->renderTemplate($exception->getStatusCode(), $request->getRequestFormat());
    }

    /**
     * @param \Throwable $throwable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createTracyResponse(Throwable $throwable): Response
    {
        Debugger::$time = time();
        $blueScreen = new BlueScreen();
        $blueScreen->info = [
            'PHP ' . PHP_VERSION,
        ];

        ob_start();
        $blueScreen->render($throwable);
        $blueScreenHtml = ob_get_clean();

        return new Response($blueScreenHtml);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function createUnableToResolveDomainResponse(Request $request): Response
    {
        $url = $request->getSchemeAndHttpHost() . $request->getBasePath();
        $content = sprintf("You are trying to access an unknown domain '%s'.", $url);

        if ($this->environment === EnvironmentType::ACCEPTANCE) {
            $content .= sprintf(" TEST environment is active, current domain url is '%s'.", $this->overwriteDomainUrl);
        }

        return new Response($content, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param \Symfony\Component\ErrorHandler\Exception\FlattenException $exception
     * @return bool
     */
    protected function isUnableToResolveDomainInNotDebug(FlattenException $exception): bool
    {
        if ($this->debug) {
            return false;
        }

        return $exception->getClass() === UnableToResolveDomainException::class;
    }

    /**
     * @param int $code
     * @param string $format
     * @return string
     */
    protected function getTemplatePath(int $code, string $format): string
    {
        return sprintf(
            'Front/Content/Error/error%s.%s.twig',
            $format === 'html' ? $code : '',
            $format
        );
    }

    /**
     * @param int $code
     * @param string|null $format
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderTemplate(int $code, ?string $format): Response
    {
        $content = $this->renderView(
            $this->getTemplatePath($code, $format),
            [
                'status_code' => $code,
                'status_text' => Response::$statusTexts[$code] ?? '',
            ]
        );

        return new Response($content, $code);
    }
}
