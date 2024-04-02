<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Transfer;

use Akeneo\Pim\ApiClient\Exception\RuntimeException;
use App\Component\Akeneo\AkeneoConfig;
use App\Component\Akeneo\Transfer\Exception\TransferException;
use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataAdministratorNonCriticalException;
use App\Model\Product\Transfer\Akeneo\Exception\FileSaveFailedException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Generator;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Model\Transfer\TransferIdentificationInterface;
use Shopsys\FrameworkBundle\Model\Transfer\TransferLoggerInterface;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractAkeneoImportTransfer implements TransferIdentificationInterface
{
    protected EntityManagerInterface $em;

    protected TransferLoggerInterface $logger;

    protected SqlLoggerFacade $sqlLoggerFacade;

    protected ValidatorInterface $validator;

    private AkeneoConfig $akeneoConfig;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     */
    public function __construct(AkeneoImportTransferDependency $akeneoImportTransferDependency)
    {
        $this->sqlLoggerFacade = $akeneoImportTransferDependency->getSqlLoggerFacade();
        $this->em = $akeneoImportTransferDependency->getEm();
        $this->validator = $akeneoImportTransferDependency->getValidator();
        $this->akeneoConfig = $akeneoImportTransferDependency->getAkeneoConfig();
        $this->logger = $akeneoImportTransferDependency
            ->getTransferLoggerFactory()
            ->getTransferLoggerByIdentifier($this);
    }

    public function runTransfer(): void
    {
        if (!$this->akeneoConfig->isEnabled()) {
            $this->logger->warning('Skipping transfer, akeneo is disabled from parameters.yml');

            return;
        }

        try {
            $this->doBeforeTransfer();

            $this->sqlLoggerFacade->temporarilyDisableLogging();

            foreach ($this->getData() as $item) {
                $this->handleExceptionsOnProcessingItem($item);
            }

            $this->sqlLoggerFacade->reenableLogging();
        } catch (RuntimeException $exception) {
            $this->logger->error('RuntimeException: ' . $exception->getMessage());
            $this->logger->persistAllLoggedTransferIssues();
            $this->sqlLoggerFacade->reenableLogging();

            return;
        }

        $this->doAfterTransfer();
        $this->logger->persistAllLoggedTransferIssues();
    }

    /**
     * @param mixed $item
     */
    private function handleExceptionsOnProcessingItem($item): void
    {
        try {
            $this->em->beginTransaction();
            $this->processItem($item);
            $this->em->commit();
        } catch (TransferInvalidDataAdministratorNonCriticalException $invalidDataSilentException) {
            $this->logger->debug(
                sprintf(
                    'Transfer of item with code `%s` was aborted because : %s',
                    $item['identifier'] ?? $item['code'],
                    $invalidDataSilentException->getMessage(),
                ),
            );
            $this->em->rollback();
        } catch (TransferInvalidDataAdministratorCriticalException $invalidDataSilentException) {
            $this->logger->warning(
                sprintf(
                    'Transfer of item with code `%s` was aborted because : %s',
                    $item['identifier'] ?? $item['code'],
                    $invalidDataSilentException->getMessage(),
                ),
            );
            $this->em->rollback();
        } catch (TransferException $transferException) {
            $this->logger->warning(
                sprintf(
                    'Transfer of item with code `%s` was aborted because : %s',
                    $item['identifier'] ?? $item['code'],
                    $transferException->getMessage(),
                ),
            );
            $this->em->rollback();
        } catch (FileSaveFailedException $transferException) {
            $this->logger->warning($transferException->getMessage());
            $this->em->rollback();
        } catch (Exception $exception) {
            $this->logger->error(
                sprintf(
                    'Transfer of item with code key `%s` was aborted. '
                    . 'This error will be reported to Shopsys. Reason of this error: %s',
                    $item['identifier'] ?? $item['code'],
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

        $this->logger->persistAllLoggedTransferIssues();
    }

    /**
     * @param mixed $akeneoData
     */
    abstract protected function processItem($akeneoData): void;

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
        return 'Akeneo';
    }

    /**
     * @return string
     */
    abstract public function getTransferIdentifier(): string;
}
