<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test;

use Shopsys\FrameworkBundle\Component\Translation\Translator;

trait SetTranslatorTrait
{
    public function setTranslator(): void
    {
        $translator = $this->createMock(Translator::class);
        $translator->method('trans')->willReturnArgument(0);

        Translator::injectSelf($translator);
    }
}
