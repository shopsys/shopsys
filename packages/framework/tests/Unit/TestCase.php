<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionClass;

class TestCase extends PHPUnitTestCase
{
    /**
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     */
    protected function setValueOfProtectedProperty(object $object, string $propertyName, mixed $value): void
    {
        $reflectionClass = new ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $reflectionProperty->setValue($object, $value);
    }
}
