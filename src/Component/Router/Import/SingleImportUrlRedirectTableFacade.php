<?php

declare(strict_types=1);

namespace App\Component\Router\Import;

use Akeneo\Pim\ApiClient\Exception\RuntimeException;
use App\Component\Domain\Domain;
use App\Model\Transfer\TransferIdentificationInterface;
use App\Model\Transfer\TransferLoggerFactory;
use App\Model\Transfer\TransferLoggerInterface;
use App\Model\UrlRedirect\UrlRedirectDataFactory;
use App\Model\UrlRedirect\UrlRedirectFacade;
use App\Model\UrlRedirect\UrlRegularFacade;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SingleImportUrlRedirectTableFacade implements TransferIdentificationInterface
{
    private const DOMAINS = [
        'cz' => 1,
        'sk' => 2,
    ];

    /**
     * @var string
     */
    private string $file;

    /**
     * @var int
     */
    private int $domainId;

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
     * @var bool
     */
    private bool $isRegular;
    /**
     * @var \App\Model\UrlRedirect\UrlRegularFacade
     */
    private UrlRegularFacade $urlRegularFacade;

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
        UrlRedirectFacade $urlRedirectFacade,
        UrlRegularFacade $urlRegularFacade
    ) {
        $this->localFilesystem = $localFilesystem;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->em = $em;
        $this->validator = $validator;
        $this->logger = $transferLoggerFactory->getTransferLoggerByIdentifier($this);
        $this->singleImportUrlRedirectTableValidator = $singleImportUrlRedirectTableValidator;
        $this->urlRedirectDataFactory = $urlRedirectDataFactory;
        $this->urlRedirectFacade = $urlRedirectFacade;
        $this->urlRegularFacade = $urlRegularFacade;
    }


    public function runTransfer(string $file, string $domain, bool $isRegular)
    {
        try {
            $this->sqlLoggerFacade->temporarilyDisableLogging();
            $this->singleImportUrlRedirectTableValidator->validateFile($file);
            $this->file = $file;
            $this->domainId = self::DOMAINS[strtolower($domain)] ?? Domain::FIRST_DOMAIN_ID;
            $this->isRegular = $isRegular;

            $this->doBeforeTransfer();

            if (strpos($this->file, '.csv')) {
                foreach ($this->getDataFromCSV() as $item) {
                    $this->handleExceptionsOnProcessingItem($item);
                }
            } else {
                foreach ($this->getData() as $item) {
                    $this->handleExceptionsOnProcessingItem($item);
                }
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
        } catch (SingleImportIrresolvableStringException $exception) {
            $this->logger->addError(
                sprintf(
                    'Import redirect url skipped. Reason of this: %s',
                    $exception->getMessage()
                )
            );

            if ($this->em->isOpen()) {
                $this->em->rollback();
            }

            $this->logger->persistAllLoggedTransferIssues();
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
        if (is_array($data)) {
            $this->singleImportUrlRedirectTableValidator->validate($data);
        } else {
            $data = $this->resolveRedirectString($data);
        }

        if ($this->isRegular) {
            $form = str_replace('/', '\/', $data['from']);
            $urlRegular = $this->urlRegularFacade->findByRegularAndDomainId($form, $this->domainId);
            if ($urlRegular === null) {
                $this->urlRegularFacade->create($form, $data['to'], $this->domainId);
            } else {
                $this->logger->addInfo(sprintf('Redirect record with regular "%s" already exists.', $form));
            }
        } else {
            $from = ltrim($data['from'], '/');
            $urlRedirect = $this->urlRedirectFacade->findByOldUrlAndDomainId($from, $this->domainId);
            if ($urlRedirect === null) {
                $urlRedirectData = $this->urlRedirectDataFactory->create();
                $urlRedirectData->oldUrl = $from;
                $urlRedirectData->newUrl = ltrim($data['to'], '/');
                $urlRedirectData->domainId = $this->domainId;
                $this->urlRedirectFacade->create($urlRedirectData);
            } else {
                $this->logger->addInfo(sprintf('Redirect record with old url "%s" already exists.', $from));
            }
        }
    }

    /**
     * @param string $redirectString
     * @return string[]
     */
    private function resolveRedirectString(string $redirectString): array
    {
        $redirectString = trim($redirectString);
        $matches = [];
        if (strpos($this->file, '.erb')) {
            $pattern = '/^rewrite \^(?P<from>[^\(\$]*)(\(\\\\\/\)\?)?(\$)? https:\/\/<%= node\[\'krieger\'\]\[\'baseurl\'\] %>(?P<to>.*) \S+;$/m';
        } else {
            throw new Exception('Unexpected file type');
        }

        $results = preg_match_all($pattern, $redirectString, $matches, PREG_SET_ORDER, 0);
        if ($results !== false && $results > 0) {
            return $matches[0];
        }

        throw new SingleImportIrresolvableStringException($redirectString);
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Import promo codes from file.');
        $this->handler = $this->localFilesystem->readStream($this->file);
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
        while (($data = fgets($this->handler)) !== false) {
            yield $data;
        }
    }

    private function getDataFromCSV(): \Generator
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
