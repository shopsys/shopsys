<?php

declare(strict_types=1);

namespace Tests\CodingStandards\Unit\Phpstan;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Shopsys\CodingStandards\Phpstan\AttributeDispatchUsageProvider;
use Tests\CodingStandards\Unit\Phpstan\Fixtures\DispatchChildFixture;
use Tests\CodingStandards\Unit\Phpstan\Fixtures\DispatchInvokableFixture;
use Tests\CodingStandards\Unit\Phpstan\Fixtures\DispatchMarker;
use Tests\CodingStandards\Unit\Phpstan\Fixtures\DispatchParentFixture;

final class AttributeDispatchUsageProviderTest extends TestCase
{
    public function testAttributeIsFoundOnTheMethodOnTheOverriddenMethodAndOnTheClassOfAnInvokable(): void
    {
        $provider = new AttributeDispatchUsageProvider([DispatchMarker::class]);

        $this->assertTrue($provider->hasAttribute(new ReflectionMethod(DispatchParentFixture::class, 'handle'), DispatchMarker::class));
        $this->assertTrue($provider->hasAttribute(new ReflectionMethod(DispatchChildFixture::class, 'handle'), DispatchMarker::class), 'overriding method keeps the dispatch of the parent');
        $this->assertTrue($provider->hasAttribute(new ReflectionMethod(DispatchInvokableFixture::class, '__invoke'), DispatchMarker::class), 'class-level attribute dispatches __invoke()');
        $this->assertFalse($provider->hasAttribute(new ReflectionMethod(DispatchParentFixture::class, 'helper'), DispatchMarker::class));
        $this->assertFalse($provider->hasAttribute(new ReflectionMethod(DispatchInvokableFixture::class, 'helper'), DispatchMarker::class), 'class-level attribute applies to __invoke() only');
    }
}
