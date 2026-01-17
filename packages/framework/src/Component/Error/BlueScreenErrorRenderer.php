<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Override;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;
use Tracy\BlueScreen;
use Tracy\Debugger;

final class BlueScreenErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ErrorRendererInterface $fallbackErrorRenderer,
        private readonly bool $isDebug,
        private readonly string $localPathToProjectRoot,
        private readonly string $tracyDebuggerIdeUrlFormat,
    ) {
    }

    #[Override]
    public function render(Throwable $exception): FlattenException
    {
        if ($this->isDebug() === false) {
            return $this->fallbackErrorRenderer->render($exception);
        }

        Debugger::$time = time();
        Debugger::$editor = $this->tracyDebuggerIdeUrlFormat;
        Debugger::$editorMapping = [
            '/var/www/html/' => $this->localPathToProjectRoot . '/',
        ];
        $blueScreen = new BlueScreen();

        ob_start();

        $blueScreen->render($exception);

        $result = ob_get_clean();

        return FlattenException::createFromThrowable($exception)->setAsString($result);
    }

    private function isDebug(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return $this->isDebug;
        }

        return $this->isDebug && $request->attributes->getBoolean('showException', true);
    }
}
