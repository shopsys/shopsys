<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\Import;

use Akeneo\Pim\ApiClient\Exception\RuntimeException;
use App\Model\Order\PromoCode\PromoCodeDataFactory;
use App\Model\Order\PromoCode\PromoCodeFacade;
use App\Model\Order\PromoCode\PromoCodeLimitFactory;
use App\Model\Transfer\TransferIdentificationInterface;
use App\Model\Transfer\TransferLoggerFactory;
use App\Model\Transfer\TransferLoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SingleImportPromoCodeFacade implements TransferIdentificationInterface
{
    private const DOMAINS = [
        'sconto_cz' => 1,
        'sconto_sk' => 2,
    ];

    private const MOEVE_CODES_BY_DOMAIN = [
        1 => 'SC',
        2 => 'SS',
    ];

    /**
     * @var array
     */
    private array $singleImportPromoCodesConfig;

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
     * @var \App\Model\Order\PromoCode\Import\SingleImportPromoCodeValidator
     */
    private SingleImportPromoCodeValidator $singleImportPromoCodeValidator;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeFacade
     */
    private PromoCodeFacade $promoCodeFacade;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeDataFactory
     */
    private PromoCodeDataFactory $promoCodeDataFactory;

    /**
     * @var \App\Model\Order\PromoCode\PromoCodeLimitFactory
     */
    private PromoCodeLimitFactory $promoCodeLimitFactory;
    /**
     * @var string
     */
    private string $displayTimezone;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Model\Transfer\TransferLoggerFactory $transferLoggerFactory
     * @param \League\Flysystem\FilesystemInterface $localFilesystem
     * @param \App\Model\Order\PromoCode\Import\SingleImportPromoCodeValidator $singleImportPromoCodeValidator
     * @param \App\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \App\Model\Order\PromoCode\PromoCodeDataFactory $promoCodeDataFactory
     * @param \App\Model\Order\PromoCode\PromoCodeLimitFactory $promoCodeLimitFactory
     */
    public function __construct(
        string $displayTimezone,
        SqlLoggerFacade $sqlLoggerFacade,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        TransferLoggerFactory $transferLoggerFactory,
        FilesystemInterface $localFilesystem,
        SingleImportPromoCodeValidator $singleImportPromoCodeValidator,
        PromoCodeFacade $promoCodeFacade,
        PromoCodeDataFactory $promoCodeDataFactory,
        PromoCodeLimitFactory $promoCodeLimitFactory
    ) {
        $this->localFilesystem = $localFilesystem;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->em = $em;
        $this->validator = $validator;
        $this->logger = $transferLoggerFactory->getTransferLoggerByIdentifier($this);
        $this->singleImportPromoCodeValidator = $singleImportPromoCodeValidator;
        $this->promoCodeFacade = $promoCodeFacade;
        $this->promoCodeDataFactory = $promoCodeDataFactory;
        $this->promoCodeLimitFactory = $promoCodeLimitFactory;
        $this->displayTimezone = $displayTimezone;
    }

    /**
     * @param array $options
     */
    public function runTransfer(array $options)
    {
        try {
            $this->sqlLoggerFacade->temporarilyDisableLogging();
            $this->singleImportPromoCodeValidator->validateOptions($options);
            $this->singleImportPromoCodesConfig = $options;

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
        $this->singleImportPromoCodeValidator->validate($data);

        $code = $data['code'];
        $domainId = $this->mapDomainIdByRow($data);
        $promoCode = $this->promoCodeFacade->findPromoCodeByCodeAndDomain($code, $domainId);

        if ($promoCode === null) {
            $promoCodeData = $this->promoCodeDataFactory->create();
            $promoCodeData->domainId = $domainId;
            $promoCodeData->code = $code;
            $promoCodeData->datetimeValidFrom = $this->mapDateTime($data['valid_from'] ?? null);
            $promoCodeData->datetimeValidTo = $this->mapDateTime($data['valid_to'] ?? null, false);
            if ($this->singleImportPromoCodesConfig['moeve_code'] === 'XX') {
                $promoCodeData->identifier = self::MOEVE_CODES_BY_DOMAIN[$domainId];
            } else {
                $promoCodeData->identifier = $this->singleImportPromoCodesConfig['moeve_code'];
            }
            $promoCodeData->onSale = $this->mapBooleanConfig($this->singleImportPromoCodesConfig['on_sale']);
            $promoCodeData->inAction = $this->mapBooleanConfig($this->singleImportPromoCodesConfig['in_action']);
            $promoCodeData->scontoPrice = $this->mapBooleanConfig($this->singleImportPromoCodesConfig['sconto_price']);
            $promoCodeData->withoutLowPrice = $this->mapBooleanConfig($this->singleImportPromoCodesConfig['without_low_price']);
            $promoCodeData->discountType = $this->singleImportPromoCodesConfig['discount_type'];

            $promoCodeLimit = $this->promoCodeLimitFactory->create(
                (string)$this->singleImportPromoCodesConfig['price_limit'],
                (string)$this->singleImportPromoCodesConfig['discount']
            );
            $promoCodeData->limits[] = $promoCodeLimit;

            $promoCode = $this->promoCodeFacade->create($promoCodeData);

            $this->logger->addInfo(sprintf('Promo code "%s" was successfully created.', $promoCode->getCode()));
        } else {
            $this->logger->addInfo(sprintf('Promo code "%s" already exists.', $promoCode->getCode()));
        }
    }

    /**
     * @param string|null $booleanString
     * @return bool
     */
    private function mapBooleanConfig(?string $booleanString): bool
    {
        if ($booleanString === null) {
            return false;
        }
        if ($booleanString === 'yes') {
            return true;
        }

        return false;
    }

    /**
     * @param string|null $dateString
     * @param bool $isFrom
     * @return \DateTime|null
     * @throws \Exception
     */
    private function mapDateTime(?string $dateString, bool $isFrom = true): ?\DateTime
    {
        if ($dateString === null) {
            return null;
        }
        $timeString = '00:00:00';
        if (!$isFrom) {
            $timeString = '23:59:59';
        }

        $datetime = new \DateTime($dateString . ' ' . $timeString, new \DateTimeZone($this->displayTimezone));
        $datetime->setTimezone(new \DateTimeZone('UTC'));

        return $datetime;
    }

    /**
     * @param array $data
     * @return int
     */
    private function mapDomainIdByRow(array $data): int
    {
        return self::DOMAINS[$data['origin']] ?? 1;
    }

    protected function doBeforeTransfer(): void
    {
        $this->logger->addInfo('Import promo codes from file.');
        $this->handler = $this->localFilesystem->readStream($this->singleImportPromoCodesConfig['file']);
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
        while (($data = fgetcsv($this->handler, 1000, ',')) !== false) {
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
        return 'single_import_promo_codes';
    }

    /**
     * @return string
     */
    public function getTransferName(): string
    {
        return 'Single import promo codes';
    }
}
