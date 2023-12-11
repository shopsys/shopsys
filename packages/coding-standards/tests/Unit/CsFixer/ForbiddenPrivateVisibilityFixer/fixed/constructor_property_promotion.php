<?php

namespace TestNamespace\TestSubNamespace;

class SomeClass
{
    public function __construct(
        protected readonly string $property1,
        protected readonly string $property2,
        public readonly string $property3,
        protected string $property4,
        protected string $property5,
        public string $property6,
    ) {
    }
}
