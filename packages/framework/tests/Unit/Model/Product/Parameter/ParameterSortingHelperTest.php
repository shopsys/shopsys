<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Parameter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterSortingHelper;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData;

class ParameterSortingHelperTest extends TestCase
{
    /**
     * @param string $locale
     * @param string[] $unsortedValues
     * @param string[] $expectedSortedValues
     */
    #[DataProvider('sortParameterValuesProvider')]
    public function testSortParameterValuesAlphabetically(
        string $locale,
        array $unsortedValues,
        array $expectedSortedValues,
    ): void {
        $parameterSortingHelper = new ParameterSortingHelper();

        $parameterValues = [];

        foreach ($unsortedValues as $valueText) {
            $parameterValues[] = $this->createParameterValue($valueText, $locale);
        }

        $sortedParameterValues = $parameterSortingHelper->sortParameterValuesAlphabetically($parameterValues, $locale);

        $sortedTexts = array_map(static fn (ParameterValue $value) => $value->getText(), $sortedParameterValues);

        $this->assertSame($expectedSortedValues, array_values($sortedTexts));
    }

    /**
     * @return iterable
     */
    public static function sortParameterValuesProvider(): iterable
    {
        yield 'Czech locale with diacritics' => [
            'locale' => 'cs',
            'unsortedValues' => ['Žlutá', 'zajíc', 'Zima', 'Alza', 'Čerstvé', 'Cihla', 'Švestka', 'Slunce', 'Broskev', 'Řípa', 'Ruka', 'Česnek', 'Chov', 'Guma', '123'],
            'expectedSortedValues' => ['123', 'Alza', 'Broskev', 'Cihla', 'Čerstvé', 'Česnek', 'Guma', 'Chov', 'Ruka', 'Řípa', 'Slunce', 'Švestka', 'zajíc', 'Zima', 'Žlutá'],
        ];

        yield 'Slovak locale with diacritics' => [
            'locale' => 'sk',
            'unsortedValues' => ['Žltá', 'Zima', 'Apple', 'Čerstvé', 'cibuľa', 'Žirafa', 'Ľadovec', 'Lampa', 'Ôsmy', 'okno', 'Banán', '123'],
            'expectedSortedValues' => ['123', 'Apple', 'Banán', 'cibuľa', 'Čerstvé', 'Ľadovec', 'Lampa', 'okno', 'Ôsmy', 'Zima', 'Žirafa', 'Žltá'],
        ];

        yield 'English locale with basic ASCII' => [
            'locale' => 'en',
            'unsortedValues' => ['Zebra', 'Apple', 'application', 'Banana', '123'],
            'expectedSortedValues' => ['123', 'Apple', 'application', 'Banana', 'Zebra'],
        ];
    }

    /**
     * @param string $text
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    private function createParameterValue(string $text, string $locale): ParameterValue
    {
        $parameterValueData = new ParameterValueData();
        $parameterValueData->text = $text;
        $parameterValueData->locale = $locale;
        $parameterValueData->colorIcon = new UploadedFileData();

        return new ParameterValue($parameterValueData);
    }
}
