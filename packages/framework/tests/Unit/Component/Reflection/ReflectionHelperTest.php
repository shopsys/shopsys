<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Reflection;

use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Tests\FrameworkBundle\Unit\Component\Reflection\Fixtures\ReflectionHelperChildFixture;
use Tests\FrameworkBundle\Unit\Component\Reflection\Fixtures\ReflectionHelperMarker;
use Tests\FrameworkBundle\Unit\Component\Reflection\Fixtures\ReflectionHelperParentFixture;
use Tests\FrameworkBundle\Unit\Component\Reflection\Fixtures\ReflectionHelperSpecialMarker;

class ReflectionHelperTest extends TestCase
{
    public function testMethodAttributesOfTheMethodItselfAreReturned(): void
    {
        $attributes = ReflectionHelper::getMethodAttributes(new ReflectionMethod(ReflectionHelperParentFixture::class, 'run'), ReflectionHelperMarker::class);

        $this->assertCount(1, $attributes);
        $this->assertSame('parent', $attributes[0]->name);
    }

    public function testOverridingMethodWithoutAttributesInheritsThemFromTheNearestDeclaration(): void
    {
        $attributes = ReflectionHelper::getMethodAttributes(new ReflectionMethod(ReflectionHelperChildFixture::class, 'run'), ReflectionHelperMarker::class);

        $this->assertCount(1, $attributes);
        $this->assertSame('parent', $attributes[0]->name);
    }

    public function testOverridingMethodWithOwnAttributesWins(): void
    {
        $attributes = ReflectionHelper::getMethodAttributes(new ReflectionMethod(ReflectionHelperChildFixture::class, 'stop'), ReflectionHelperMarker::class);

        $this->assertCount(1, $attributes);
        $this->assertSame('child', $attributes[0]->name);
    }

    public function testMethodWithoutAttributesAndWithoutPrototypeReturnsNothing(): void
    {
        $this->assertSame([], ReflectionHelper::getMethodAttributes(new ReflectionMethod(ReflectionHelperParentFixture::class, 'plain'), ReflectionHelperMarker::class));
    }

    public function testInstanceOfFlagMatchesSubclassesOfTheAttribute(): void
    {
        $attributes = ReflectionHelper::getMethodAttributes(new ReflectionMethod(ReflectionHelperChildFixture::class, 'special'), ReflectionHelperMarker::class, ReflectionAttribute::IS_INSTANCEOF);

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(ReflectionHelperSpecialMarker::class, $attributes[0]);
    }

    public function testClassAttributeIsLookedUpInTheParents(): void
    {
        $this->assertSame('parent-class', ReflectionHelper::getClassAttribute(new ReflectionClass(ReflectionHelperParentFixture::class), ReflectionHelperMarker::class)?->name);
        $this->assertSame('parent-class', ReflectionHelper::getClassAttribute(new ReflectionClass(ReflectionHelperChildFixture::class), ReflectionHelperMarker::class)?->name);
        $this->assertNull(ReflectionHelper::getClassAttribute(new ReflectionClass(self::class), ReflectionHelperMarker::class));
    }
}
