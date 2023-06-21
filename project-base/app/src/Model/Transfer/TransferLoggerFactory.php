<?php

declare(strict_types=1);

namespace App\Model\Transfer;

use App\Model\Transfer\Exception\UnknownServiceTransferException;
use App\Model\Transfer\Issue\TransferIssueFacade;
use Symfony\Bridge\Monolog\Logger;

class TransferLoggerFactory
{
    /**
     * @var \App\Model\Transfer\TransferLoggerInterface[]
     */
    private array $transferLoggers = [];

    /**
     * @param \Symfony\Bridge\Monolog\Logger $defaultLogger
     * @param \App\Model\Transfer\TransferFacade $transferFacade
     * @param \App\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade
     */
    public function __construct(
        private Logger $defaultLogger,
        private TransferFacade $transferFacade,
        private TransferIssueFacade $transferIssueFacade,
    ) {
    }

    /**
     * @param \App\Model\Transfer\TransferIdentificationInterface $transferIdentification
     * @return \App\Model\Transfer\TransferLoggerInterface
     */
    public function getTransferLoggerByIdentifier(
        TransferIdentificationInterface $transferIdentification,
    ): TransferLoggerInterface {
        $serviceTransferIdentifier = $transferIdentification->getServiceIdentifier() . ucfirst($transferIdentification->getTransferIdentifier());
        $serviceTransferName = $transferIdentification->getServiceIdentifier() . ' ' . ucfirst($transferIdentification->getTransferName());

        if (array_key_exists($serviceTransferIdentifier, $this->transferLoggers)) {
            return $this->transferLoggers[$serviceTransferIdentifier];
        }

        try {
            $this->transferFacade->getTransferByIdentifier($serviceTransferIdentifier);
        } catch (UnknownServiceTransferException $exception) {
            $this->transferFacade->create($serviceTransferIdentifier, $serviceTransferName);
        }

        $newLogger = new TransferLogger(
            $this->defaultLogger,
            $serviceTransferIdentifier,
            $this->transferIssueFacade,
        );

        $this->transferLoggers[$serviceTransferIdentifier] = $newLogger;

        return $newLogger;
    }
}
