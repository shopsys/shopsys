<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

use Shopsys\FrameworkBundle\Model\Transfer\Exception\UnknownServiceTransferException;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueDataFactory;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade;
use Symfony\Bridge\Monolog\Logger;

class TransferLoggerFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transfer\TransferLoggerInterface[]
     */
    protected array $transferLoggers = [];

    /**
     * @param \Symfony\Bridge\Monolog\Logger $defaultLogger
     * @param \Shopsys\FrameworkBundle\Model\Transfer\TransferFacade $transferFacade
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueDataFactory $transferIssueDataFactory
     */
    public function __construct(
        protected readonly Logger $defaultLogger,
        protected readonly TransferFacade $transferFacade,
        protected readonly TransferIssueFacade $transferIssueFacade,
        protected readonly TransferIssueDataFactory $transferIssueDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transfer\TransferIdentificationInterface $transferIdentification
     * @return \Shopsys\FrameworkBundle\Model\Transfer\TransferLoggerInterface
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
            $this->transferIssueDataFactory,
        );

        $this->transferLoggers[$serviceTransferIdentifier] = $newLogger;

        return $newLogger;
    }
}
