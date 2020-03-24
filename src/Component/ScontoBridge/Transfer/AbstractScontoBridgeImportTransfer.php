<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge\Transfer;

use App\Component\ScontoBridge\Transfer\Exception\TransferException;
use App\Component\ScontoBridge\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use App\Component\ScontoBridge\Transfer\Exception\TransferInvalidDataAdministratorNonCriticalException;
use App\Model\Transfer\TransferIdentificationInterface;
use Exception;
use Generator;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException;
use Symfony\Component\Validator\Validator\TraceableValidator;

abstract class AbstractScontoBridgeImportTransfer implements TransferIdentificationInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \App\Model\Transfer\TransferLoggerInterface
     */
    protected $logger;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    protected $sqlLoggerFacade;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * @var \App\Component\ScontoBridge\ScontoBridgeConfig
     */
    private $scontoBridgeConfig;

    /**
     * @var int|null
     */
    public $cronBatchSize = null;

    /**
     * @param \App\Component\ScontoBridge\Transfer\ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency
     */
    public function __construct(ScontoBridgeImportTransferDependency $scontoBridgeImportTransferDependency)
    {
        $this->sqlLoggerFacade = $scontoBridgeImportTransferDependency->getSqlLoggerFacade();
        $this->em = $scontoBridgeImportTransferDependency->getEm();
        $this->logger = $scontoBridgeImportTransferDependency->getTransferLoggerFactory()->getTransferLoggerByIdentifier($this);
        $this->validator = $scontoBridgeImportTransferDependency->getValidator();
        $this->scontoBridgeConfig = $scontoBridgeImportTransferDependency->getScontoBridgeConfig();
    }

    /**
     * @return bool
     */
    public function runTransfer(): bool
    {
        if (!$this->scontoBridgeConfig->isEnabled()) {
            $this->logger->addWarning('Skipping transfer, sconto bridge is disabled from parameters.yml');
            return false;
        }

        $this->doBeforeTransfer();

        $scontoBridgeData = $this->getData();
        $runNextIteration = $this->processItems($scontoBridgeData);

        $this->doAfterTransfer();
        $this->logger->persistAllLoggedTransferIssues();

        return $runNextIteration;
    }

    /**
     * @param \Generator $scontoBridgeData
     * @return bool
     */
    protected function processItems(Generator $scontoBridgeData): bool
    {
        $this->sqlLoggerFacade->temporarilyDisableLogging();
        $processed = 1;

        foreach ($scontoBridgeData as $item) {
            try {
                $this->em->beginTransaction();
                $this->processItem($item);
                $this->em->commit();
            } catch (TransferInvalidDataAdministratorNonCriticalException $invalidDataSilentException) {
                $this->logger->addDebug(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        $item['erpCustomerNumber'],
                        $invalidDataSilentException->getMessage()
                    )
                );
                $this->em->rollback();
            } catch (TransferInvalidDataAdministratorCriticalException $invalidDataSilentException) {
                $this->logger->addWarning(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        $item['erpCustomerNumber'],
                        $invalidDataSilentException->getMessage()
                    )
                );
                $this->em->rollback();
            } catch (DuplicateEmailException $invalidDataSilentException) {
                $this->logger->addWarning(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        $item['erpCustomerNumber'],
                        $invalidDataSilentException->getMessage()
                    )
                );
                $this->em->rollback();
            } catch (TransferException $transferException) {
                $this->logger->addWarning(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        $item['erpCustomerNumber'],
                        $transferException->getMessage()
                    )
                );
                $this->em->rollback();
            } catch (Exception $exception) {
                $this->logger->addError(
                    sprintf(
                        'Transfer of item with code key `%s` was aborted. '
                        . 'This error will be reported to Shopsys. Reason of this error: %s',
                        $item['erpCustomerNumber'],
                        $exception->getMessage()
                    )
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
     * @param array $scontoBridgeData
     */
    abstract protected function processItem(array $scontoBridgeData): void;

    abstract protected function doBeforeTransfer(): void;

    abstract protected function doAfterTransfer(): void;

    abstract public function cronSleep(): void;

    abstract public function cronWakeUp(): void;

    /**
     * @return \Generator
     */
    abstract protected function getData(): \Generator;

    /**
     * @return string
     */
    public function getServiceIdentifier(): string
    {
        return 'ScontoBridge';
    }
}
