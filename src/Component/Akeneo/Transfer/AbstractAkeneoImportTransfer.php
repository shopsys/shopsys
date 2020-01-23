<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Transfer;

use Akeneo\Pim\ApiClient\Exception\RuntimeException;
use App\Component\Akeneo\Transfer\Exception\TransferException;
use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataAdministratorCriticalException;
use App\Component\Akeneo\Transfer\Exception\TransferInvalidDataAdministratorNonCriticalException;
use Exception;
use Generator;
use Symfony\Component\Validator\Validator\TraceableValidator;

abstract class AbstractAkeneoImportTransfer
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
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
     * @var \App\Component\Akeneo\AkeneoConfig
     */
    private $akeneoConfig;

    /**
     * @param \App\Component\Akeneo\Transfer\AkeneoImportTransferDependency $akeneoImportTransferDependency
     */
    public function __construct(AkeneoImportTransferDependency $akeneoImportTransferDependency)
    {
        $this->sqlLoggerFacade = $akeneoImportTransferDependency->getSqlLoggerFacade();
        $this->em = $akeneoImportTransferDependency->getEm();
        $this->logger = $akeneoImportTransferDependency->getLogger();
        $this->validator = $akeneoImportTransferDependency->getValidator();
        $this->akeneoConfig = $akeneoImportTransferDependency->getAkeneoConfig();
    }

    public function runTransfer(): void
    {
        if (!$this->akeneoConfig->isEnabled()) {
            $this->logger->addWarning('Skipping transfer, akeneo is disabled from parameters.yml');
            return;
        }

        $this->doBeforeTransfer();

        try {
            $akeneoData = $this->getData();
            $this->processItems($akeneoData);
        } catch (RuntimeException $exception) {
            $this->logger->addError($exception);
            return;
        }

        $this->doAfterTransfer();
    }

    /**
     * @param \Generator $akeneoData
     */
    protected function processItems(Generator $akeneoData): void
    {
        $this->sqlLoggerFacade->temporarilyDisableLogging();

        foreach ($akeneoData as $item) {
            try {
                $this->em->beginTransaction();
                $this->processItem($item);
                $this->em->commit();
            } catch (TransferInvalidDataAdministratorNonCriticalException $invalidDataSilentException) {
                $this->logger->addDebug(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        $item['identifier'] ?? $item['code'],
                        $invalidDataSilentException->getMessage()
                    )
                );
                $this->em->rollback();
            } catch (TransferInvalidDataAdministratorCriticalException $invalidDataSilentException) {
                $this->logger->addWarning(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        $item['identifier'] ?? $item['code'],
                        $invalidDataSilentException->getMessage()
                    )
                );
                $this->em->rollback();
            } catch (TransferException $transferException) {
                $this->logger->addWarning(
                    sprintf(
                        'Transfer of item with code `%s` was aborted because : %s',
                        $item['identifier'] ?? $item['code'],
                        $transferException->getMessage()
                    )
                );
                $this->em->rollback();
            } catch (Exception $exception) {
                $this->logger->addError(
                    sprintf(
                        'Transfer of item with code key `%s` was aborted. '
                        . 'This error will be reported to Shopsys. Reason of this error: %s',
                        $item['identifier'] ?? $item['code'],
                        $exception->getMessage()
                    )
                );

                $this->sqlLoggerFacade->reenableLogging();

                if ($this->em->isOpen()) {
                    $this->em->rollback();
                }

                throw $exception;
            } finally {
                $this->em->clear();

                if ($this->validator instanceof TraceableValidator) {
                    $this->validator->reset();
                }
            }
        }

        $this->sqlLoggerFacade->reenableLogging();
    }

    /**
     * @param array $akeneoProductData
     */
    abstract protected function processItem(array $akeneoProductData): void;

    abstract protected function doBeforeTransfer(): void;

    abstract protected function doAfterTransfer(): void;

    /**
     * @return \Generator
     */
    abstract protected function getData(): \Generator;
}
