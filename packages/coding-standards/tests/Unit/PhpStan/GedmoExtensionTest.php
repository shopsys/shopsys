<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\PhpStan;

use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Testing\PHPStanTestCase;
use Shopsys\CodingStandards\Phpstan\GedmoExtension;

class GedmoExtensionTest extends PHPStanTestCase
{
    private ReflectionProvider $reflectionProvider;

    private GedmoExtension $extension;

    /**
     * @return iterable
     */
    public function getProperties(): iterable
    {
        yield ['parent', true];

        yield ['level', true];

        yield ['lft', true];

        yield ['rgt', true];

        yield ['name', false];

        yield ['children', false];
    }

    protected function setUp(): void
    {
        $this->reflectionProvider = $this->createReflectionProvider();

        $this->extension = new GedmoExtension();
    }

    /**
     * @dataProvider getProperties
     * @param string $propertyName
     * @param bool $isWritten
     */
    public function testPropertyIsProperlyReported(string $propertyName, bool $isWritten): void
    {
        $classReflection = $this->reflectionProvider->getClass(GedmoTestEntity::class);

        $property = $classReflection->getNativeProperty($propertyName);

        self::assertEquals($this->extension->isAlwaysWritten($property, $propertyName), $isWritten);
    }
}
