<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\CsFixer\Phpdoc\MissingReturnAnnotationFixer;

use Tests\CodingStandards\Unit\CsFixer\Phpdoc\FunctionAnnotationFixer\Source\Naming;

/**
 * @covers \Shopsys\CodingStandards\CsFixer\Phpdoc\MissingReturnAnnotationFixer
 */
final class FunctionAnnotationFixerTestCorrectClass extends Naming
{
    /**
     * $I->pressKeysByElement($element, [[\Facebook\WebDriver\WebDriverKeys, 'day'], 1]); // DAY1
     *
     * @param \Facebook\WebDriver\WebDriverElement $element
     * @param string|string[] $keys
     */
    public function pressKeysByElement(WebDriverElement $element, $keys)
    {
    }
}
