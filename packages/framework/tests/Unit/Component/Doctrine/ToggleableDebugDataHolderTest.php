<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Doctrine;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Doctrine\ToggleableDebugDataHolder;
use Symfony\Bridge\Doctrine\Middleware\Debug\DebugDataHolder;
use Symfony\Bridge\Doctrine\Middleware\Debug\Query;

class ToggleableDebugDataHolderTest extends TestCase
{
    public function testQueriesAreDelegatedToInnerHolderWhenEnabled(): void
    {
        $innerDebugDataHolder = new DebugDataHolder();
        $toggleableDebugDataHolder = new ToggleableDebugDataHolder($innerDebugDataHolder);

        $toggleableDebugDataHolder->addQuery('default', new Query('SELECT 1'));

        $this->assertCount(1, $innerDebugDataHolder->getData()['default']);
        $this->assertSame($innerDebugDataHolder->getData(), $toggleableDebugDataHolder->getData());
    }

    public function testQueriesAreNotCollectedWhenDisabled(): void
    {
        $innerDebugDataHolder = new DebugDataHolder();
        $toggleableDebugDataHolder = new ToggleableDebugDataHolder($innerDebugDataHolder);

        $toggleableDebugDataHolder->addQuery('default', new Query('SELECT 1'));
        $toggleableDebugDataHolder->disable();
        $toggleableDebugDataHolder->addQuery('default', new Query('SELECT 2'));

        $this->assertSame([], $toggleableDebugDataHolder->getData());
        $this->assertSame([], $innerDebugDataHolder->getData());

        $toggleableDebugDataHolder->enable();
        $toggleableDebugDataHolder->addQuery('default', new Query('SELECT 3'));

        $data = $innerDebugDataHolder->getData();
        $this->assertArrayHasKey('default', $data);
        $this->assertSame('SELECT 3', $data['default'][0]['sql']);
    }
}
