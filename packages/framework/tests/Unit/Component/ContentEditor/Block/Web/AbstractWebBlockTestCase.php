<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ContentEditor\Block\Web;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

/**
 * Blocks translate their labels with t(), which needs the static translator
 */
abstract class AbstractWebBlockTestCase extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $translator = $this->createStub(Translator::class);
        $translator->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translator);
    }
}
