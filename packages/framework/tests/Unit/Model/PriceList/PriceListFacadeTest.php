<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\PriceList;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\PriceList\PriceListFacade;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

class PriceListFacadeTest extends TestCase
{
    /**
     * The list of characters CsvEncoder escapes by is private and may change with any Symfony release,
     * so this test derives the escaping from the encoder itself instead of repeating the list
     */
    #[DataProvider('exportedValueDataProvider')]
    public function testUnescapeFormulaIsInverseToCsvEncoderEscaping(string $value): void
    {
        $this->assertSame($value, $this->unescapeFormula($this->exportValue($value)));
    }

    /**
     * @return iterable<string, array{value: string}>
     */
    public static function exportedValueDataProvider(): iterable
    {
        foreach (range(1, 127) as $characterCode) {
            yield sprintf('value starting with the character 0x%02X', $characterCode) => [
                'value' => chr($characterCode) . '500A',
            ];
        }

        yield 'formula hidden behind a leading space' => [
            'value' => ' =1+1',
        ];

        yield 'formula hidden behind a leading non-breaking space' => [
            'value' => "\u{00A0}=1+1",
        ];

        yield 'formula hidden behind a leading zero-width no-break space' => [
            'value' => "\u{FEFF}=1+1",
        ];

        yield 'common value' => [
            'value' => '500A',
        ];

        yield 'empty value' => [
            'value' => '',
        ];
    }

    /**
     * A cell holding an apostrophe followed by a formula trigger character cannot be told apart from an escaped
     * value, so it is read as escaped — a value that really starts with an apostrophe loses it on import
     */
    public function testApostropheIsRemovedFromValueThatCannotBeToldApartFromAnEscapedOne(): void
    {
        $this->assertSame('=1+1', $this->unescapeFormula("'=1+1"));
    }

    private function unescapeFormula(string $value): string
    {
        $priceListFacade = new class() extends PriceListFacade {
            public function __construct()
            {
            }

            #[Override]
            public function unescapeFormula(string $value): string
            {
                return parent::unescapeFormula($value);
            }
        };

        return $priceListFacade->unescapeFormula($value);
    }

    /**
     * Returns the value as the CSV export writes it into the file
     */
    private function exportValue(string $value): string
    {
        $csvEncoder = new CsvEncoder();
        $data = [['value' => $value]];
        $context = [CsvEncoder::NO_HEADERS_KEY => true];

        $escapedCsv = $csvEncoder->encode($data, CsvEncoder::FORMAT, $context + [CsvEncoder::ESCAPE_FORMULAS_KEY => true]);

        if ($escapedCsv === $csvEncoder->encode($data, CsvEncoder::FORMAT, $context)) {
            return $value;
        }

        return "'" . $value;
    }
}
