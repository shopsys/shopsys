<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\ProductPricesMulticurrencyModeProvider;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class PriceListFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly PriceListFactory $priceListFactory,
        protected readonly PriceListRepository $priceListRepository,
        protected readonly PriceListProductPriceFactory $priceListProductPriceFactory,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly PriceListCsvColumnsEnum $priceListExportColumnsEnum,
        protected readonly FileUpload $fileUpload,
        protected readonly ValidatorInterface $validator,
        protected readonly PriceListProductPriceDataFactory $priceListProductPriceDataFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly ImportPriceListResultFactory $importPriceListResultFactory,
        protected readonly MountManager $mountManager,
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly ProductPricesMulticurrencyModeProvider $productPricesMulticurrencyModeProvider,
    ) {
    }

    public function getById(int $id): PriceList
    {
        return $this->priceListRepository->getById($id);
    }

    public function getPriceListGridQueryBuilder(): QueryBuilder
    {
        return $this->priceListRepository->getPriceListGridQueryBuilder();
    }

    public function create(PriceListData $priceListData): PriceList
    {
        $priceList = $this->priceListFactory->create($priceListData);
        $this->em->persist($priceList);
        $this->em->flush();

        $this->refreshPriceListProductPrices($priceList, $priceListData);

        return $priceList;
    }

    public function edit(int $priceListId, PriceListData $priceListData): PriceList
    {
        $priceList = $this->getById($priceListId);
        $originalProductIds = $priceList->getProductIds();

        $priceList->edit($priceListData);

        $this->em->flush();

        $this->refreshPriceListProductPrices($priceList, $priceListData, $originalProductIds);

        return $priceList;
    }

    /**
     * @param int[] $originalProductIds
     */
    protected function refreshPriceListProductPrices(
        PriceList $priceList,
        PriceListData $priceListData,
        array $originalProductIds = [],
    ): void {
        $dispatchedProductIds = [];

        foreach ($priceListData->priceListProductPricesData as $priceListProductPriceData) {
            $priceListProductPrice = $this->priceListProductPriceFactory->create($priceListProductPriceData);
            $this->em->persist($priceListProductPrice);
            $priceList->addPriceListProductPrice($priceListProductPrice);

            $this->productRecalculationDispatcher->dispatchSingleProductId(
                $priceListProductPrice->getProduct()->getId(),
                ProductRecalculationPriorityEnum::HIGH,
                [ProductExportScopeConfig::SCOPE_PRICE],
            );

            $dispatchedProductIds[] = $priceListProductPrice->getProduct()->getId();
        }

        $removedProductIds = array_diff($originalProductIds, $dispatchedProductIds);

        $this->productRecalculationDispatcher->dispatchProductIds(
            $removedProductIds,
            ProductRecalculationPriorityEnum::HIGH,
            [ProductExportScopeConfig::SCOPE_PRICE],
        );

        $this->em->flush();
    }

    public function delete(int $priceListId): void
    {
        $priceList = $this->getById($priceListId);

        $this->productRecalculationDispatcher->dispatchProductIds(
            $priceList->getProductIds(),
            ProductRecalculationPriorityEnum::HIGH,
            [ProductExportScopeConfig::SCOPE_PRICE],
        );

        $this->em->remove($priceList);
        $this->em->flush();
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getPriceListDataToExport(int $priceListId): array
    {
        $data = [];
        $exportColumns = array_flip($this->priceListExportColumnsEnum->getAllCases());

        foreach ($this->priceListRepository->getPriceListDataToExport($priceListId) as $priceListWithProducts) {
            $priceListWithProducts[PriceListCsvColumnsEnum::PRICE] = $this->normalizePriceColumn($priceListWithProducts[PriceListCsvColumnsEnum::PRICE]);
            $data[] = array_intersect_key($priceListWithProducts, $exportColumns);
        }

        return $data;
    }

    protected function normalizePriceColumn(?Money $priceAmount): string
    {
        if ($priceAmount === null) {
            return '';
        }

        return (string)(float)$priceAmount->getAmount();
    }

    public function importPriceList(
        PriceListData $priceListData,
        UploadedFileData $uploadedFileData,
    ): ImportPriceListResult {
        $importResult = $this->importPriceListResultFactory->create();
        $csvEncoder = new CsvEncoder();

        try {
            $fileContent = $this->getFileContent($uploadedFileData);
        } catch (Throwable) {
            $importResult->addError(1, t('Cannot read file.'));

            return $importResult;
        }

        $delimiter = $this->guessDelimiter($fileContent);

        $data = $csvEncoder->decode($fileContent, CsvEncoder::FORMAT, [CsvEncoder::DELIMITER_KEY => $delimiter]);

        $constraints = $this->createConstraints();

        if (count($data) === 0 || !$this->areColumnsValid($data[0])) {
            $importResult->addError(
                1,
                t(
                    'Invalid header in the CSV file. Use following columns: {{ columns }}',
                    ['{{ columns }}' => implode(', ', $this->priceListExportColumnsEnum->getAllCases())],
                    Translator::VALIDATOR_TRANSLATION_DOMAIN,
                ),
            );

            return $importResult;
        }

        $line = 1; // first line is header
        $priceListData->priceListProductPricesData = [];

        foreach ($data as $row) {
            $line++;
            $this->processCsvRow($row, $constraints, $importResult, $priceListData, $line);
        }

        if ($this->productPricesMulticurrencyModeProvider->isManualMode()) {
            $this->removeProductPricesWithIncompleteCurrencyCoverage($priceListData, $importResult);
        }

        if ($priceListData->id) {
            $priceList = $this->edit($priceListData->id, $priceListData);
        } else {
            $priceList = $this->create($priceListData);
        }

        $importResult->setPriceListInfo($priceList);

        return $importResult;
    }

    /**
     * @param array<string, string> $row
     */
    protected function areColumnsValid(array $row): bool
    {
        $columnNames = array_keys($row);

        return array_diff($this->priceListExportColumnsEnum->getAllCases(), $columnNames) === [];
    }

    protected function createConstraints(): Constraints\Collection
    {
        $fields = [
            PriceListCsvColumnsEnum::PRODUCT_CATNUM => [
                new Constraints\NotBlank(
                    message: t(
                        'column {{ column_name }} cannot be empty',
                        ['{{ column_name }}' => PriceListCsvColumnsEnum::PRODUCT_CATNUM],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                ),
            ],
            PriceListCsvColumnsEnum::PRICE => [
                new Constraints\Type(
                    type: 'numeric',
                    message: t(
                        'column {{ column_name }} must be number, {{ value }} provided',
                        ['{{ column_name }}' => PriceListCsvColumnsEnum::PRICE],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                ),
                new Constraints\GreaterThan(
                    value: 0,
                    message: t(
                        'column {{ column_name }} must be greater than 0',
                        ['{{ column_name }}' => PriceListCsvColumnsEnum::PRICE],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                ),
            ],
        ];

        if ($this->productPricesMulticurrencyModeProvider->isManualMode()) {
            $fields[PriceListCsvColumnsEnum::CURRENCY_CODE] = [
                new Constraints\NotBlank(
                    message: t(
                        'column {{ column_name }} cannot be empty',
                        ['{{ column_name }}' => PriceListCsvColumnsEnum::CURRENCY_CODE],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                ),
            ];
        }

        return new Constraints\Collection(
            $fields,
            allowExtraFields: true,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PriceList\PriceList[]
     */
    public function getAll(): array
    {
        return $this->priceListRepository->getAll();
    }

    protected function guessDelimiter(string $fileContent): string
    {
        $firstLine = strtok($fileContent, "\n");

        if (str_contains($firstLine, ';')) {
            return ';';
        }

        return ',';
    }

    protected function preProcessCsvRow(array $row): array
    {
        if (!array_key_exists(PriceListCsvColumnsEnum::PRICE, $row)) {
            return $row;
        }

        $row[PriceListCsvColumnsEnum::PRICE] = str_replace(',', '.', $row[PriceListCsvColumnsEnum::PRICE]);

        return $row;
    }

    protected function processCsvRow(
        array $rawRow,
        Constraints\Collection $constraints,
        ImportPriceListResult $importResult,
        PriceListData $priceListData,
        int $line,
    ): void {
        $row = $this->preProcessCsvRow($rawRow);
        $violations = $this->validator->validate($row, $constraints);

        foreach ($violations as $violation) {
            $importResult->addWarning($line, $violation->getMessage());
        }

        if ($violations->count() > 0) {
            return;
        }

        $product = $this->productFacade->findByCatnum($row[PriceListCsvColumnsEnum::PRODUCT_CATNUM]);

        if ($product === null) {
            $importResult->addWarning(
                $line,
                t(
                    'Product with catnum {{ catnum }} not found',
                    ['{{ catnum }}' => $row[PriceListCsvColumnsEnum::PRODUCT_CATNUM]],
                    Translator::VALIDATOR_TRANSLATION_DOMAIN,
                ),
            );

            return;
        }

        $currency = null;

        if ($this->productPricesMulticurrencyModeProvider->isManualMode()) {
            $currencyCode = $row[PriceListCsvColumnsEnum::CURRENCY_CODE];

            if (!$this->domain->getDomainConfigById($priceListData->domainId)->hasCurrencyCode($currencyCode)) {
                $importResult->addWarning(
                    $line,
                    t(
                        'column {{ column_name }} contains currency code {{ currency_code }} that is not enabled on the domain',
                        [
                            '{{ column_name }}' => PriceListCsvColumnsEnum::CURRENCY_CODE,
                            '{{ currency_code }}' => $currencyCode,
                        ],
                        Translator::VALIDATOR_TRANSLATION_DOMAIN,
                    ),
                );

                return;
            }

            $currency = $this->currencyFacade->getByCode($currencyCode);
        }

        $priceListData->priceListProductPricesData[] = $this->priceListProductPriceDataFactory->create(
            $product,
            Money::create($row[PriceListCsvColumnsEnum::PRICE]),
            $priceListData->domainId,
            $currency,
        );

        $importResult->increaseSuccessfulCount();
    }

    protected function removeProductPricesWithIncompleteCurrencyCoverage(
        PriceListData $priceListData,
        ImportPriceListResult $importResult,
    ): void {
        $requiredCurrencyCodes = $this->domain->getDomainConfigById($priceListData->domainId)->getCurrencyCodes();
        $currencyCodesByProductId = [];

        foreach ($priceListData->priceListProductPricesData as $priceListProductPriceData) {
            $currencyCodesByProductId[$priceListProductPriceData->product->getId()][] = $priceListProductPriceData->currency->getCode();
        }

        $shouldWarnByIncompleteProductId = [];

        foreach ($currencyCodesByProductId as $productId => $currencyCodes) {
            if (array_diff($requiredCurrencyCodes, $currencyCodes) !== []) {
                $shouldWarnByIncompleteProductId[$productId] = true;
            }
        }

        if ($shouldWarnByIncompleteProductId === []) {
            return;
        }

        foreach ($priceListData->priceListProductPricesData as $key => $priceListProductPriceData) {
            $product = $priceListProductPriceData->product;

            if (!array_key_exists($product->getId(), $shouldWarnByIncompleteProductId)) {
                continue;
            }

            unset($priceListData->priceListProductPricesData[$key]);
            $importResult->decreaseSuccessfulCount();

            if (!$shouldWarnByIncompleteProductId[$product->getId()]) {
                continue;
            }

            $importResult->addGeneralWarning(
                t(
                    'Product with catnum {{ catnum }} does not have prices for all currencies of the domain ({{ currency_codes }}), its prices were skipped',
                    [
                        '{{ catnum }}' => $product->getCatnum(),
                        '{{ currency_codes }}' => implode(', ', $requiredCurrencyCodes),
                    ],
                    Translator::VALIDATOR_TRANSLATION_DOMAIN,
                ),
            );
            $shouldWarnByIncompleteProductId[$product->getId()] = false;
        }

        $priceListData->priceListProductPricesData = array_values($priceListData->priceListProductPricesData);
    }

    protected function getFileContent(UploadedFileData $uploadedFileData): string
    {
        $path = $this->fileUpload->getTemporaryFilepathForMountManager(array_first($uploadedFileData->uploadedFiles));

        return $this->mountManager->read('main://' . $path);
    }
}
