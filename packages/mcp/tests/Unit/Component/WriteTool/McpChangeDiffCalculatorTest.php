<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Component\WriteTool;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\McpBundle\Component\WriteTool\McpChangeDiffCalculator;

class McpChangeDiffCalculatorTest extends TestCase
{
    /**
     * @param array<int, array{type: string, text: string}> $expectedSegments
     */
    #[DataProvider('diffProvider')]
    #[DataProvider('edgeCaseProvider')]
    #[DataProvider('cleanupProvider')]
    public function testCalculate(?string $before, ?string $after, array $expectedSegments): void
    {
        $this->assertSame($expectedSegments, new McpChangeDiffCalculator()->calculate($before, $after));
    }

    public static function diffProvider(): Iterator
    {
        yield 'changed word in a sentence' => [
            'but in my view the company',
            'but the company',
            [
                ['type' => 'equal', 'text' => 'but '],
                ['type' => 'deleted', 'text' => 'in my view '],
                ['type' => 'equal', 'text' => 'the company'],
            ],
        ];

        yield 'replaced word' => [
            'so it already ends',
            'so it finally ends',
            [
                ['type' => 'equal', 'text' => 'so it '],
                ['type' => 'deleted', 'text' => 'already'],
                ['type' => 'inserted', 'text' => 'finally'],
                ['type' => 'equal', 'text' => ' ends'],
            ],
        ];

        yield 'HTML tags are compared separately from the text' => [
            '<p>Old</p>',
            '<p>New</p>',
            [
                ['type' => 'equal', 'text' => '<p>'],
                ['type' => 'deleted', 'text' => 'Old'],
                ['type' => 'inserted', 'text' => 'New'],
                ['type' => 'equal', 'text' => '</p>'],
            ],
        ];

        yield 'added value' => [
            null,
            'New name',
            [
                ['type' => 'inserted', 'text' => 'New name'],
            ],
        ];

        yield 'removed value' => [
            'Old name',
            null,
            [
                ['type' => 'deleted', 'text' => 'Old name'],
            ],
        ];
    }

    public static function edgeCaseProvider(): Iterator
    {
        yield 'unchanged value' => [
            'Same name',
            'Same name',
            [
                ['type' => 'equal', 'text' => 'Same name'],
            ],
        ];

        yield 'no value before and after' => [
            null,
            null,
            [],
        ];

        yield 'multibyte text' => [
            'Příliš žluťoučký kůň',
            'Příliš žluťoučký pes',
            [
                ['type' => 'equal', 'text' => 'Příliš žluťoučký '],
                ['type' => 'deleted', 'text' => 'kůň'],
                ['type' => 'inserted', 'text' => 'pes'],
            ],
        ];

        yield 'whitespace change' => [
            'a  b',
            'a b',
            [
                ['type' => 'equal', 'text' => 'a'],
                ['type' => 'deleted', 'text' => '  '],
                ['type' => 'inserted', 'text' => ' '],
                ['type' => 'equal', 'text' => 'b'],
            ],
        ];
    }

    public static function cleanupProvider(): Iterator
    {
        yield 'rewritten text is shown as a whole replacement' => [
            'Current world and the memory crisis',
            'A developer\'s view on memory prices',
            [
                ['type' => 'deleted', 'text' => 'Current world and the memory crisis'],
                ['type' => 'inserted', 'text' => 'A developer\'s view on memory prices'],
            ],
        ];

        yield 'single equal word between changes is part of the change' => [
            'We sell red shoes and blue hats in our big shop',
            'We sell green boots and yellow hats in our big shop',
            [
                ['type' => 'equal', 'text' => 'We sell '],
                ['type' => 'deleted', 'text' => 'red shoes and blue'],
                ['type' => 'inserted', 'text' => 'green boots and yellow'],
                ['type' => 'equal', 'text' => ' hats in our big shop'],
            ],
        ];

        yield 'single equal word at the end is kept' => [
            'red shoes',
            'blue shoes',
            [
                ['type' => 'deleted', 'text' => 'red'],
                ['type' => 'inserted', 'text' => 'blue'],
                ['type' => 'equal', 'text' => ' shoes'],
            ],
        ];

        $longText = implode(' ', array_fill(0, 500, 'word'));

        yield 'too long text is shown as a whole replacement' => [
            $longText,
            $longText . ' end',
            [
                ['type' => 'deleted', 'text' => $longText],
                ['type' => 'inserted', 'text' => $longText . ' end'],
            ],
        ];
    }
}
