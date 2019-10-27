<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Log;

use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Tracy\BlueScreen;
use Tracy\Logger;

class TracyFileLogger
{
    /**
     * @var string
     */
    protected $logDirectory;

    /**
     * @var \Tracy\Logger
     */
    protected $tracyLogger;

    /**
     * @var \Tracy\BlueScreen
     */
    protected $blueScreen;

    /**
     * @var string
     */
    private $environment;

    /**
     * @param string $logDirectory
     * @param \Tracy\Logger $tracyLogger
     * @param \Tracy\BlueScreen $blueScreen
     */
    public function __construct(string $logDirectory, string $environment, Logger $tracyLogger, BlueScreen $blueScreen)
    {
        $this->logDirectory = $logDirectory;
        $this->environment = $environment;
        $this->tracyLogger = $tracyLogger;
        $this->blueScreen = $blueScreen;
    }

    /**
     * @param \Throwable $exception
     */
    public function logToFile(\Throwable $exception): void
    {
        if ($this->environment !== EnvironmentType::PRODUCTION) {
            return;
        }

        if ($this->tracyLogger->directory === null) {
            $this->tracyLogger->directory = $this->logDirectory;
        }

        if ($this->tracyLogger->directory !== null
            && is_dir($this->tracyLogger->directory)
            && is_writable($this->tracyLogger->directory)
        ) {
            $exceptionFile = $this->tracyLogger->getExceptionFile($exception);
            $this->blueScreen->renderToFile($exception, $exceptionFile);
        }
    }
}
