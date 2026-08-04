<?php

declare(strict_types=1);

namespace Tests\App\Unit\Model\Product\Search;

use App\Model\Product\Elasticsearch\ProductExportDataProvider;
use InvalidArgumentException;
use Nette\Utils\Json;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportDataProviderInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchConverter;
use Shopsys\FrameworkBundle\Model\ProductReview\Elasticsearch\ProductReviewDocumentMapper;
use Shopsys\ProductFeed\ZboziBundle\Model\Product\Elasticsearch\ZboziProductExportDataProvider;
use Symfony\Component\Finder\Finder;

class ProductElasticsearchConverterTest extends TestCase
{
    #[DataProvider('getProductMappingFiles')]
    public function testAllFieldsAreMentionedInConverter(string $mappingFile): void
    {
        $productElasticsearchConverter = new ProductElasticsearchConverter(
            new ProductReviewDocumentMapper(),
            [$this->createProductExportDataProvider()],
        );

        $product = [
            'parameters' => [[]],
        ];
        $filledProduct = $productElasticsearchConverter->fillEmptyFields($product);

        $mapping = Json::decode(
            file_get_contents($mappingFile),
            true,
        );

        $productFieldsInMapping = array_keys($mapping['mappings']['properties']);
        $productFieldsFromConverter = array_keys($filledProduct);

        $this->checkMapping($productFieldsInMapping, $productFieldsFromConverter, 'fillEmptyFields()', $mappingFile);

        $parameterFieldsInMapping = array_keys($mapping['mappings']['properties']['parameters']['properties']);
        $parameterFieldsFromConverter = array_keys($filledProduct['parameters'][0]);

        $this->checkMapping($parameterFieldsInMapping, $parameterFieldsFromConverter, 'fillEmptyParameters()', $mappingFile);
    }

    private function checkMapping(
        array $mappingFields,
        array $converterFields,
        string $methodName,
        string $mappingFile,
    ): void {
        $missingFieldsInConverter = array_diff($mappingFields, $converterFields);
        $message = 'Following fields are missing in the ProductElasticsearchConverter::' . $methodName . ' method, while mentioned in ' . $mappingFile . ': ';
        $message .= "\n[\n  " . implode("\n  ", $missingFieldsInConverter) . "\n]";

        $this->assertCount(0, $missingFieldsInConverter, $message);

        $missingFieldsInMapping = array_diff($converterFields, $mappingFields);
        $message = 'Following fields are missing in the ' . $mappingFile . ', while mentioned in ProductElasticsearchConverter::' . $methodName . ' method: ';
        $message .= "\n[\n  " . implode("\n  ", $missingFieldsInMapping) . "\n]";

        $this->assertCount(0, $missingFieldsInMapping, $message);
    }

    public static function getProductMappingFiles(): iterable
    {
        $finder = Finder::create()
            ->files()
            ->in(__DIR__ . '/../../../../../../src/Resources/definition/product/')
            ->name('*.json')
            ->sortByName();

        foreach ($finder as $file) {
            yield $file->getFilename() => [$file->getRealPath()];
        }
    }

    private function createProductExportDataProvider(): ProductExportDataProviderInterface
    {
        return new class() implements ProductExportDataProviderInterface {
            /**
             * {@inheritdoc}
             */
            #[Override]
            public function getExportFields(): array
            {
                return [
                    ProductExportDataProvider::MAIN_CATEGORY_PATH,
                    ProductExportDataProvider::USPS,
                    ProductExportDataProvider::SEARCHING_NAMES,
                    ProductExportDataProvider::SEARCHING_DESCRIPTIONS,
                    ProductExportDataProvider::SEARCHING_CATNUMS,
                    ProductExportDataProvider::SEARCHING_EANS,
                    ProductExportDataProvider::SEARCHING_PARTNOS,
                    ProductExportDataProvider::SEARCHING_SHORT_DESCRIPTIONS,
                    ProductExportDataProvider::BREADCRUMB,
                    ZboziProductExportDataProvider::ZBOZI_CATEGORY,
                ];
            }

            /**
             * {@inheritdoc}
             */
            #[Override]
            public function getExportScopeRules(): array
            {
                return [];
            }

            /**
             * {@inheritdoc}
             */
            #[Override]
            public function loadProductExportData(array $products, int $domainId, string $locale): void
            {
            }

            #[Override]
            public function getExportedFieldValue(
                Product $product,
                int $domainId,
                string $locale,
                string $field,
            ): mixed {
                return null;
            }

            #[Override]
            public function getDefaultValue(string $field): mixed
            {
                return match ($field) {
                    ProductExportDataProvider::MAIN_CATEGORY_PATH,
                    ProductExportDataProvider::SEARCHING_NAMES,
                    ProductExportDataProvider::SEARCHING_DESCRIPTIONS,
                    ProductExportDataProvider::SEARCHING_CATNUMS,
                    ProductExportDataProvider::SEARCHING_EANS,
                    ProductExportDataProvider::SEARCHING_PARTNOS,
                    ProductExportDataProvider::SEARCHING_SHORT_DESCRIPTIONS => '',
                    ProductExportDataProvider::USPS,
                    ProductExportDataProvider::BREADCRUMB => [],
                    ZboziProductExportDataProvider::ZBOZI_CATEGORY => null,
                    default => throw new InvalidArgumentException(sprintf('There is no default value for "%s" Elasticsearch field', $field)),
                };
            }
        };
    }
}
