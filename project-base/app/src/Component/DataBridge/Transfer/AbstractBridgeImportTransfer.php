<?php

declare(strict_types=1);

namespace App\Component\DataBridge\Transfer;

use App\Component\DataBridge\BridgeConfig;
use App\Component\DataBridge\Transfer\Exception\TransferException;
use App\Component\DataBridge\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use App\Component\DataBridge\Transfer\Exception\TransferInvalidDataAdministratorNonCriticalException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Generator;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Model\Transfer\TransferIdentificationInterface;
use Shopsys\FrameworkBundle\Model\Transfer\TransferLoggerInterface;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractBridgeImportTransfer implements TransferIdentificationInterface
{
    protected EntityManagerInterface $em;

    protected TransferLoggerInterface $logger;

    protected SqlLoggerFacade $sqlLoggerFacade;

    protected ValidatorInterface $validator;

    private BridgeConfig $bridgeConfig;

    public ?int $cronBatchSize = null;

    /**
     * @param \App\Component\DataBridge\Transfer\BridgeImportTransferDependency $bridgeImportTransferDependency
     */
    public function __construct(BridgeImportTransferDependency $bridgeImportTransferDependency)
    {
        $this->sqlLoggerFacade = $bridgeImportTransferDependency->getSqlLoggerFacade();
        $this->em = $bridgeImportTransferDependency->getEm();
        $this->logger = $bridgeImportTransferDependency->getTransferLoggerFactory()->getTransferLoggerByIdentifier($this);
        $this->validator = $bridgeImportTransferDependency->getValidator();
        $this->bridgeConfig = $bridgeImportTransferDependency->getBridgeConfig();
    }

    /**
     * @return bool
     */
    public function runTransfer(): bool
    {
        if (!$this->bridgeConfig->isEnabled()) {
            $this->logger->warning('Skipping transfer, data bridge is disabled from parameters.yml');

            return false;
        }

        $this->doBeforeTransfer();

        $bridgeData = $this->getData();
        $runNextIteration = $this->processItems($bridgeData);

        $this->doAfterTransfer();
        $this->logger->persistAllLoggedTransferIssues();

        return $runNextIteration;
    }

    /**
     * @param \Generator $bridgeData
     * @return bool
     */
    protected function processItems(Generator $bridgeData): bool
    {
        $this->sqlLoggerFacade->temporarilyDisableLogging();
        $processed = 1;

        foreach ($bridgeData as $item) {
            try {
                $this->em->beginTransaction();
                $this->processItem($item);
                $this->em->commit();
            } catch (TransferInvalidDataAdministratorNonCriticalException $invalidDataSilentException) {
                $this->logger->debug(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        json_encode($item),
                        $invalidDataSilentException->getMessage(),
                    ),
                );
                $this->em->rollback();
            } catch (TransferInvalidDataAdministratorCriticalException $invalidDataSilentException) {
                $this->logger->warning(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        json_encode($item),
                        $invalidDataSilentException->getMessage(),
                    ),
                );
                $this->em->rollback();
            } catch (TransferException $transferException) {
                $this->logger->warning(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        json_encode($item),
                        $transferException->getMessage(),
                    ),
                );
                $this->em->rollback();
            } catch (Exception $exception) {
                $this->logger->error(
                    sprintf(
                        'Transfer of item with code key `%s` was aborted. '
                        . 'This error will be reported to Shopsys. Reason of this error: %s',
                        json_encode($item),
                        $exception->getMessage(),
                    ),
                );

                $this->sqlLoggerFacade->reenableLogging();

                if ($this->em->isOpen()) {
                    $this->em->rollback();
                }
                $this->logger->persistAllLoggedTransferIssues();

                throw $exception;
            } finally {
                $this->em->clear();

                if ($this->validator instanceof TraceableValidator) {
                    $this->validator->reset();
                }

                $this->logger->persistAllLoggedTransferIssues();
            }

            if ($processed === $this->cronBatchSize) {
                $this->sqlLoggerFacade->reenableLogging();

                return true;
            }
            $processed++;
        }

        $this->logger->persistAllLoggedTransferIssues();

        $this->sqlLoggerFacade->reenableLogging();

        return false;
    }

    /**
     * @param array $bridgeData
     */
    abstract protected function processItem(array $bridgeData): void;

    abstract protected function doBeforeTransfer(): void;

    abstract protected function doAfterTransfer(): void;

    /**
     * @return \Generator
     */
    abstract protected function getData(): Generator;

    /**
     * @return string
     */
    public function getServiceIdentifier(): string
    {
        return 'DataBridge';
    }
}
