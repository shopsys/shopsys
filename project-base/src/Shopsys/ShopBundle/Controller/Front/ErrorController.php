<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Exception;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade;
use Shopsys\FrameworkBundle\Component\Error\ExceptionController;
use Shopsys\FrameworkBundle\Component\Error\ExceptionListener;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
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

    public function __construct(
        ExceptionController $exceptionController,
        ExceptionListener $exceptionListener,
        ErrorPagesFacade $errorPagesFacade,
        Domain $domain
    ) {
        $this->exceptionController = $exceptionController;
        $this->exceptionListener = $exceptionListener;
        $this->errorPagesFacade = $errorPagesFacade;
        $this->domain = $domain;
    }
    
    public function errorPageAction(int $code): void
    {
        $this->exceptionController->setDebug(false);
        $this->exceptionController->setShowErrorPagePrototype();

        throw new \Shopsys\FrameworkBundle\Component\Error\Exception\FakeHttpException($code);
    }

    public function showAction(
        Request $request,
        FlattenException $exception,
        DebugLoggerInterface $logger = null
    ) {
        if ($this->exceptionController->isShownErrorPagePrototype()) {
            return $this->createErrorPagePrototypeResponse($request, $exception, $logger);
        } elseif ($this->exceptionController->getDebug()) {
            return $this->createExceptionResponse($request, $exception, $logger);
        } else {
            return $this->createErrorPageResponse($exception->getStatusCode());
        }
    }

    private function createErrorPagePrototypeResponse(
        Request $request,
        FlattenException $exception,
        DebugLoggerInterface $logger
    ): \Symfony\Component\HttpFoundation\Response {
        // Same as in \Symfony\Bundle\TwigBundle\Controller\PreviewErrorController
        $format = $request->getRequestFormat();

        $code = $exception->getStatusCode();

        return $this->render('@ShopsysShop/Front/Content/Error/error.' . $format . '.twig', [
            'status_code' => $code,
            'status_text' => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
            'exception' => $exception,
            'logger' => $logger,
        ]);
    }
    
    private function createErrorPageResponse(int $statusCode): \Symfony\Component\HttpFoundation\Response
    {
        $errorPageStatusCode = $this->errorPagesFacade->getErrorPageStatusCodeByStatusCode($statusCode);
        $errorPageContent = $this->errorPagesFacade->getErrorPageContentByDomainIdAndStatusCode(
            $this->domain->getId(),
            $errorPageStatusCode
        );

        return new Response($errorPageContent, $errorPageStatusCode);
    }

    private function createExceptionResponse(Request $request, FlattenException $exception, DebugLoggerInterface $logger): \Symfony\Component\HttpFoundation\Response
    {
        $lastException = $this->exceptionListener->getLastException();
        if ($lastException !== null) {
            return $this->getPrettyExceptionResponse($lastException);
        }

        return $this->exceptionController->showAction($request, $exception, $logger);
    }

    private function getPrettyExceptionResponse(Exception $exception): \Symfony\Component\HttpFoundation\Response
    {
        Debugger::$time = time();
        $blueScreen = new BlueScreen();
        $blueScreen->info = [
            'PHP ' . PHP_VERSION,
        ];

        ob_start();
        $blueScreen->render($exception);
        $blueScreenHtml = ob_get_contents();
        ob_end_clean();

        return new Response($blueScreenHtml);
    }
}
