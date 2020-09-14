<?php

declare(strict_types=1);

namespace App\Component\Router\Import;

use Akeneo\Pim\ApiClient\Exception\RuntimeException;
use App\Model\Transfer\TransferIdentificationInterface;
use App\Model\Transfer\TransferLoggerFactory;
use App\Model\Transfer\TransferLoggerInterface;
use App\Model\UrlRedirect\UrlRedirectDataFactory;
use App\Model\UrlRedirect\UrlRedirectFacade;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SingleImportUrlRedirectTableFacade implements TransferIdentificationInterface
{
    private const DOMAINS = [
        'sconto_cz' => 1,
        'sconto_sk' => 2,
    ];

    /**
     * @var array
     */
    private array $commandConfig;

    /**
     * @var \League\Flysystem\FilesystemInterface
     */
    private FilesystemInterface $localFilesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    private SqlLoggerFacade $sqlLoggerFacade;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @var \App\Model\Transfer\TransferLoggerInterface
     */
    private TransferLoggerInterface $logger;

    /**
     * @var resource
     */
    private $handler;

    /**
     * @var \App\Component\Router\Import\SingleImportUrlRedirectTableValidator
     */
    private SingleImportUrlRedirectTableValidator $singleImportUrlRedirectTableValidator;

    /**
     * @var \App\Model\UrlRedirect\UrlRedirectDataFactory
     */
    private UrlRedirectDataFactory $urlRedirectDataFactory;

    /**
     * @var \App\Model\UrlRedirect\UrlRedirectFacade
     */
    private UrlRedirectFacade $urlRedirectFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Model\Transfer\TransferLoggerFactory $transferLoggerFactory
     * @param \League\Flysystem\FilesystemInterface $localFilesystem
     * @param \App\Component\Router\Import\SingleImportUrlRedirectTableValidator $singleImportUrlRedirectTableValidator
     * @param \App\Model\UrlRedirect\UrlRedirectDataFactory $urlRedirectDataFactory
     * @param \App\Model\UrlRedirect\UrlRedirectFacade $urlRedirectFacade
     */
    public function __construct(
        SqlLoggerFacade $sqlLoggerFacade,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        TransferLoggerFactory $transferLoggerFactory,
        FilesystemInterface $localFilesystem,
        SingleImportUrlRedirectTableValidator $singleImportUrlRedirectTableValidator,
        UrlRedirectDataFactory $urlRedirectDataFactory,
        UrlRedirectFacade $urlRedirectFacade
    ) {
        $this->localFilesystem = $localFilesystem;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->em = $em;
        $this->validator = $validator;
        $this->logger = $transferLoggerFactory->getTransferLoggerByIdentifier($this);
        $this->singleImportUrlRedirectTableValidator = $singleImportUrlRedirectTableValidator;
        $this->urlRedirectDataFactory = $urlRedirectDataFactory;
        $this->urlRedirectFacade = $urlRedirectFacade;
    }

    /**
     * @param array $options
     */
    public function runTransfer(string $file)
    {
        try {
            $this->sqlLoggerFacade->temporarilyDisableLogging();
            $this->singleImportUrlRedirectTableValidator->validateFile($options);
            $this->commandConfig = $options;

            $this->doBeforeTransfer();

            foreach ($this->getData() as $item) {
                $this->handleExceptionsOnProcessingItem($item);
            }

            $this->sqlLoggerFacade->reenableLogging();
        } catch (RuntimeException $exception) {
            $this->logger->addError('RuntimeException: ' . $exception->getMessage());
            $this->sqlLoggerFacade->reenableLogging();
        } catch (Exception $exception) {
            $this->logger->addError('Exception: ' . $exception->getMessage());
            $this->sqlLoggerFacade->reenableLogging();
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
        } catch (Exception $exception) {
            $this->logger->addError(
                sprintf(
                    'Import promo codes failed. Reason of this error: %s',
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

        $this->logger->persistAllLoggedTransferIssues();
    }

    /**
     * @param mixed $data
     */
    protected function processItem($data): void
    {
        $this->singleImportUrlRedirectTableValidator->validate($data);

        $urlRedirect = $this->urlRedirectFacade->findByOldUrl($data['from']);
        if ($urlRedirect === null) {
            $urlRedirectData = $this->urlRedirectDataFactory->create();
            $urlRedirectData->oldUrl = ltrim($data['from']);
            $urlRedirectData->newUrl = ltrim($data['to']);
            $this->urlRedirectFacade->create($urlRedirectData);

            $this->logger->addInfo('Redirect record was successfully created.');
        } else {
            $this->logger->addInfo(sprintf('Redirect record with old url "%s" already exists.', $data['from']));
        }
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Import promo codes from file.');
        $this->handler = $this->localFilesystem->readStream($this->commandConfig['file']);
    }

    protected function doAfterTransfer(): void
    {
        $this->logger->addInfo('Import is done.');
    }

    /**
     * @return \Generator
     */
    protected function getData(): \Generator
    {
        $keys = [];
        $isFirstLine = true;
        $expectedColumnsCount = 0;
        while (($data = fgetcsv($this->handler, 0, ',')) !== false) {
            if ($isFirstLine) {
                $keys = $data;
                $isFirstLine = false;
                $expectedColumnsCount = count($data);
                continue;
            }

            if (count($data) !== $expectedColumnsCount) {
                throw new SingleImportStructureException($expectedColumnsCount, count($data));
            }

            $data = array_combine($keys, $data);

            yield $data;
        }
    }

    /**
     * @return string
     */
    public function getServiceIdentifier(): string
    {
        return 'System';
    }

    /**
     * @return string
     */
    public function getTransferIdentifier(): string
    {
        return 'single_import_url_redirect_table';
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return 'Single import url redirect table';
    }
}
