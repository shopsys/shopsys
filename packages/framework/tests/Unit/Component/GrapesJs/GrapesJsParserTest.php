<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\GrapesJs;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\GrapesJs\GrapesJsParser;

class GrapesJsParserTest extends TestCase
{
    /**
     * @param string $inputText
     * @param string $expectedOutput
     */
    #[DataProvider('parseDataProvider')]
    public function testParse(string $inputText, string $expectedOutput): void
    {
        $grapesJsParser = new GrapesJsParser();
        $this->assertSame($expectedOutput, $grapesJsParser->parse($inputText));
    }

    /**
     * @return iterable
     */
    public static function parseDataProvider(): iterable
    {
        yield 'drop products' => [
            '<div class="gjs-products" data-products="1, 2, 3"><div class="gjs-product" data-product="1"></div><div class="gjs-product" data-product="2"><div class="gjs-product" data-product="3"></div>',
            '|||[gjc-comp-ProductList&#61;1,2,3]|||',
        ];

        yield 'simple products' => [
            '<div class="gjs-products" data-products="1, 2, 3"></div>',
            '|||[gjc-comp-ProductList&#61;1,2,3]|||',
        ];

        yield 'random attributes' => [
            '<div id="test" data-products="1, 2, 3" tabindex="0" style="background-color: #f0f0f0;" class="gjs-products" title="Test Div"></div>',
            '|||[gjc-comp-ProductList&#61;1,2,3]|||',
        ];

        yield 'invalid products field' => [
            '<div class="gjs-products">lorem</div>',
            '<div class="gjs-products">lorem</div>',
        ];

        yield 'no products' => [
            '<div class="gjs-text-ckeditor" data-gjs-type="text">description - Lorem ipsum dolor sit amet,</div><div class="gjs-text-ckeditor" data-gjs-type="text">adipiscing elit. Vivamus felis nisi.<br>Vivamus pulvinar sem non auctor dictum.<br>Morbi eleifend semper enim, eu faucibus tortor posuere vitae.<br></div><div class="gjs-text-ckeditor" data-gjs-type="text">consectetur</div>',
            '<div class="gjs-text-ckeditor">description - Lorem ipsum dolor sit amet,</div><div class="gjs-text-ckeditor">adipiscing elit. Vivamus felis nisi.<br />Vivamus pulvinar sem non auctor dictum.<br />Morbi eleifend semper enim, eu faucibus tortor posuere vitae.<br /></div><div class="gjs-text-ckeditor">consectetur</div>',
        ];

        yield 'multiple lines' => [
            <<<EOT
            <div class="gjs-text-ckeditor" data-gjs-type="text">description - Lorem ipsum dolor sit amet,</div>
            <div class="gjs-text-ckeditor" data-gjs-type="text">adipiscing elit. Vivamus felis nisi.<br>
                Vivamus pulvinar sem non auctor dictum.<br>
                Morbi eleifend semper enim, eu faucibus tortor posuere vitae.<br>
            </div>
            <div class="gjs-text-ckeditor" data-gjs-type="text">consectetur</div>
            EOT,
            <<<EOT
            <div class="gjs-text-ckeditor">description - Lorem ipsum dolor sit amet,</div>
            <div class="gjs-text-ckeditor">adipiscing elit. Vivamus felis nisi.<br />
                Vivamus pulvinar sem non auctor dictum.<br />
                Morbi eleifend semper enim, eu faucibus tortor posuere vitae.<br />
            </div>
            <div class="gjs-text-ckeditor">consectetur</div>
            EOT,
        ];

        yield 'products in columns' => [
            '<div class="gjs-text-ckeditor"> description - Lorem ipsum dolor sit amet, </div><div class="row"><div class="column"><div data-products="1" class="gjs-products" style="text-align: center;"><div data-product="1" data-product-name="unknown catalog number (product will not be displayed)" class="gjs-product" style="display: inline-block; width: 20%; margin-top: 1em; margin-right: 1em; margin-bottom: 1em; margin-left: 1em;"></div></div></div><div class="column"><div data-products="2" class="gjs-products" style="text-align: center;"><div data-product="2" data-product-name="unknown catalog number (product will not be displayed)" class="gjs-product" style="display: inline-block; width: 20%; margin-top: 1em; margin-right: 1em; margin-bottom: 1em; margin-left: 1em;"></div></div></div></div><div class="gjs-text-ckeditor"> consectetur </div>',
            '<div class="gjs-text-ckeditor"> description - Lorem ipsum dolor sit amet, </div><div class="row"><div class="column">|||[gjc-comp-ProductList&#61;1]|||</div><div class="column">|||[gjc-comp-ProductList&#61;2]|||</div></div><div class="gjs-text-ckeditor"> consectetur </div>',
        ];
    }
}
