<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\UnableToResolveDomainException;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade;
use Shopsys\FrameworkBundle\Component\Error\Exception\FakeHttpException;
use Shopsys\FrameworkBundle\Component\Error\ExceptionController;
use Shopsys\FrameworkBundle\Component\Error\ExceptionListener;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Throwable;
use Tracy\BlueScreen;
use Tracy\Debugger;

class ErrorController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Error\ExceptionController
     */
    private $exceptionController;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Error\ExceptionListener
     */
    private $exceptionListener;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade
     */
    private $errorPagesFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var string|null
     */
    private ?string $overwriteDomainUrl;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Error\ExceptionController $exceptionController
     * @param \Shopsys\FrameworkBundle\Component\Error\ExceptionListener $exceptionListener
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade $errorPagesFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param string $environment
     * @param string|null $overwriteDomainUrl
     */
    public function __construct(
        ExceptionController $exceptionController,
        ExceptionListener $exceptionListener,
        ErrorPagesFacade $errorPagesFacade,
        Domain $domain,
        string $environment,
        ?string $overwriteDomainUrl = null
    ) {
        $this->exceptionController = $exceptionController;
        $this->exceptionListener = $exceptionListener;
        $this->errorPagesFacade = $errorPagesFacade;
        $this->domain = $domain;
        $this->environment = $environment;
        $this->overwriteDomainUrl = $overwriteDomainUrl;
    }

    /**
     * @param int $code
     */
    public function errorPageAction($code)
    {
        $this->exceptionController->setDebug(false);
        $this->exceptionController->setShowErrorPagePrototype();

        throw new FakeHttpException((int)$code);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\ErrorHandler\Exception\FlattenException $exception
     * @param \Symfony\Component\HttpKernel\Log\DebugLoggerInterface|null $logger
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(
        Request $request,
        FlattenException $exception,
        ?DebugLoggerInterface $logger = null
    ) {
        if ($this->isUnableToResolveDomainInNotDebug($exception)) {
            return $this->createUnableToResolveDomainResponse($request);
        }

        if ($this->exceptionController->isShownErrorPagePrototype()) {
            return $this->createErrorPagePrototypeResponse($request, $exception, $logger);
        }

        if ($this->exceptionController->getDebug()) {
            return $this->createExceptionResponse($request, $exception, $logger);
        }
        return $this->createErrorPageResponse($exception->getStatusCode());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\ErrorHandler\Exception\FlattenException $exception
     * @param \Symfony\Component\HttpKernel\Log\DebugLoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createErrorPagePrototypeResponse(
        Request $request,
        FlattenException $exception,
        DebugLoggerInterface $logger
    ) {
        // Same as in \Symfony\Bundle\TwigBundle\Controller\PreviewErrorController
        $format = $request->getRequestFormat();

        $code = $exception->getStatusCode();

        return $this->render($this->getTemplatePath($code, $format), [
            'status_code' => $code,
            'status_text' => Response::$statusTexts[$code] ?? '',
            'exception' => $exception,
            'logger' => $logger,
        ]);
    }

    /**
     * @param int $code
     * @param string $format
     * @return string
     */
    private function getTemplatePath(int $code, string $format): string
    {
        return sprintf(
            'Front/Content/Error/error%s.%s.twig',
            $format === 'html' ? $code : '',
            $format
        );
    }

    /**
     * @param int $statusCode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createErrorPageResponse($statusCode)
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
     * @param \Symfony\Component\HttpKernel\Log\DebugLoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createExceptionResponse(Request $request, FlattenException $exception, DebugLoggerInterface $logger)
    {
        $lastThrowable = $this->exceptionListener->getLastThrowable();
        if ($lastThrowable !== null) {
            return $this->getPrettyExceptionResponse($lastThrowable);
        }

        return $this->exceptionController->showAction($request, $exception, $logger);
    }

    /**
     * @param \Throwable $throwable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getPrettyExceptionResponse(Throwable $throwable)
    {
        Debugger::$time = time();
        $blueScreen = new BlueScreen();
        $blueScreen->info = [
            'PHP ' . PHP_VERSION,
        ];

        ob_start();
        $blueScreen->render($throwable);
        $blueScreenHtml = ob_get_contents();
        ob_end_clean();

        return new Response($blueScreenHtml);
    }

    /**
     * @param \Symfony\Component\ErrorHandler\Exception\FlattenException $exception
     * @return bool
     */
    private function isUnableToResolveDomainInNotDebug(FlattenException $exception): bool
    {
        if ($this->exceptionController->getDebug()) {
            return false;
        }

        return $exception->getClass() === UnableToResolveDomainException::class;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function createUnableToResolveDomainResponse(Request $request): Response
    {
        $url = $request->getSchemeAndHttpHost() . $request->getBasePath();
        $content = sprintf("You are trying to access an unknown domain '%s'.", $url);

        if ($this->environment === EnvironmentType::ACCEPTANCE) {
            $content .= sprintf(" TEST environment is active, current domain url is '%s'.", $this->overwriteDomainUrl);
        }

        return new Response($content, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
