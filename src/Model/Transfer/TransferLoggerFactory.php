<?php

declare(strict_types=1);

namespace App\Model\Transfer;

use App\Model\Transfer\Issue\TransferIssueFacade;
use Symfony\Bridge\Monolog\Logger;

class TransferLoggerFactory
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $defaultLogger;

    /**
     * @var \App\Model\Transfer\TransferLoggerInterface[]
     */
    private $transferLoggers = [];

    /**
     * @var \App\Model\Transfer\TransferRepository
     */
    private $transferRepository;

    /**
     * @var \App\Model\Transfer\Issue\TransferIssueFacade
     */
    private $transferIssueFacade;

    /**
     * @param \Symfony\Bridge\Monolog\Logger $defaultLogger
     * @param \App\Model\Transfer\TransferRepository $transferRepository
     * @param \App\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade
     */
    public function __construct(
        Logger $defaultLogger,
        TransferRepository $transferRepository,
        TransferIssueFacade $transferIssueFacade
    ) {
        $this->defaultLogger = $defaultLogger;
        $this->transferRepository = $transferRepository;
        $this->transferIssueFacade = $transferIssueFacade;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param string $identifier
     * @return \App\Model\Transfer\TransferLoggerInterface
     */
    private function create(Logger $logger, string $identifier): TransferLoggerInterface
    {
        return new TransferLogger(
            $logger,
            $identifier,
            $this->transferRepository,
            $this->transferIssueFacade
        );
    }

    /**
     * @param string $identifier
     * @return \App\Model\Transfer\TransferLoggerInterface
     */
    public function getTransferLoggerByIdentifier(string $identifier): TransferLoggerInterface
    {
        if (array_key_exists($identifier, $this->transferLoggers)) {
            return $this->transferLoggers[$identifier];
        }

        $newLogger = $this->create($this->defaultLogger, $identifier);
        $this->transferLoggers[$identifier] = $newLogger;

        return $newLogger;
    }
}
