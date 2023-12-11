<?php

namespace TestNamespace\TestSubNamespace;

class SomeClass
{
    public function __construct(
        private readonly string $property1,
        protected readonly string $property2,
        public readonly string $property3,
        private string $property4,
        protected string $property5,
        public string $property6,
    ) {
    }
}
