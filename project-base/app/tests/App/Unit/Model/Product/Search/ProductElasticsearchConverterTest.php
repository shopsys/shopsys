<?php

declare(strict_types=1);

namespace Tests\App\Unit\Model\Product\Search;

use App\Model\Product\Search\ProductElasticsearchConverter;
use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;

class ProductElasticsearchConverterTest extends TestCase
{
    /**
     * @dataProvider getProductMappingFiles
     * @param string $mappingFile
     */
    public function testAllFieldsAreMentionedInConverter(string $mappingFile): void
    {
        $productElasticsearchConverter = new ProductElasticsearchConverter();

        $product = [
            'parameters' => [[]],
        ];
        $filledProduct = $productElasticsearchConverter->fillEmptyFields($product);

        $mapping = Json::decode(
            file_get_contents($mappingFile),
            Json::FORCE_ARRAY,
        );

        $productFieldsInMapping = array_keys($mapping['mappings']['properties']);
        $productFieldsFromConverter = array_keys($filledProduct);

        $this->checkMapping($productFieldsInMapping, $productFieldsFromConverter, 'fillEmptyFields()', $mappingFile);

        $parameterFieldsInMapping = array_keys($mapping['mappings']['properties']['parameters']['properties']);
        $parameterFieldsFromConverter = array_keys($filledProduct['parameters'][0]);

        $this->checkMapping($parameterFieldsInMapping, $parameterFieldsFromConverter, 'fillEmptyParameters()', $mappingFile);
    }

    /**
     * @param mixed[] $mappingFields
     * @param mixed[] $converterFields
     * @param string $methodName
     * @param string $mappingFile
     */
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

    /**
     * @return iterable
     */
    public function getProductMappingFiles(): iterable
    {
        yield [realpath(__DIR__ . '/../../../../../../src/Resources/definition/product/1.json')];

        yield [realpath(__DIR__ . '/../../../../../../src/Resources/definition/product/2.json')];
    }
}
