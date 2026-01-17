<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

        foreach ($this->priceListRepository->getPriceListDataToExport($priceListId) as $priceListWithProducts) {
            $priceListWithProducts[PriceListCsvColumnsEnum::PRICE] = $this->normalizePriceColumn($priceListWithProducts[PriceListCsvColumnsEnum::PRICE]);
            $data[] = $priceListWithProducts;
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

        $path = $this->fileUpload->getAbsoluteTemporaryFilepath(reset($uploadedFileData->uploadedFiles));
        $fileContent = file_get_contents($path);

        if ($fileContent === false) {
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

            $row = $this->preProcessCsvRow($row);
            $violations = $this->validator->validate($row, $constraints);

            foreach ($violations as $violation) {
                $importResult->addWarning($line, $violation->getMessage());
            }

            if ($violations->count() > 0) {
                continue;
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

                continue;
            }

            $priceListData->priceListProductPricesData[] = $this->priceListProductPriceDataFactory->create(
                $product,
                Money::create($row[PriceListCsvColumnsEnum::PRICE]),
                $priceListData->domainId,
            );

            $importResult->increaseSuccessfulCount();
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

    /**
     * @return \Symfony\Component\Validator\Constraints\Collection
     */
    protected function createConstraints(): Constraints\Collection
    {
        return new Constraints\Collection(
            [
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
            ],
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
}
