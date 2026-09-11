<?php

declare(strict_types=1);

namespace App\Model;

use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\DummyFacade;
use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\DummyStatusEnum;
use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\DummyValue;
use Tests\CodingStandards\Unit\CsFixer\ExtendedClassNameResolverFixer\Source\FinalDummyFacade;

class Example extends DummyValue
{
    public function run($other): void
    {
        DummyFacade::doSomething('a');
        $callable = DummyFacade::doSomething(...);
        FinalDummyFacade::run();
        DummyStatusEnum::from('active');
        DummyValue::create();
        $constant = DummyFacade::CONSTANT;
        $property = DummyFacade::$property;
        $className = DummyFacade::class;
        $instance = new DummyFacade();
        self::helper();
        static::helper();
        parent::create();
        $other::doSomething('b');
        $isFacade = $other instanceof DummyFacade;
    }

    public static function helper(): void
    {
    }
}
