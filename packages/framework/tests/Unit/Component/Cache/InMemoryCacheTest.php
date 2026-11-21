<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cache;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\Exception\NamespaceCacheKeyNotFoundException;
use Shopsys\FrameworkBundle\Component\Cache\Exception\ValueCacheKeyNotFoundException;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;

class InMemoryCacheTest extends TestCase
{
    public function testGetAndSaveValue(): void
    {
        $getValueCallback = fn () => 4 * 5;
        $namespace = 'test';
        $expectedValue = $getValueCallback();

        $inMemoryCache = new InMemoryCache();

        $value = $inMemoryCache->getOrSaveValue($namespace, $getValueCallback, 123, 'zxc@example.com', true, 78.9);
        $this->assertSame($expectedValue, $value);
    }

    public function testGetKeyFromParts(): void
    {
        $expectedKey = '123~zxc~example~com~1~78~9';
        $inMemoryCache = new InMemoryCache();
        $key = $inMemoryCache->getKey(123, 'zxc@example.com', true, 78.9);

        $this->assertSame($expectedKey, $key);
    }

    public function testSave(): void
    {
        $namespace = 'test';
        $key = 'first';
        $expectedValue = 'value';

        $inMemoryCache = new InMemoryCache();
        $inMemoryCache->save($namespace, $expectedValue, $key);

        $value = $inMemoryCache->getItem($namespace, $key);
        $this->assertSame($expectedValue, $value);
    }

    public function testDelete(): void
    {
        $namespace = 'test';
        $key = 'first';
        $expectedValue = 'value';

        $inMemoryCache = new InMemoryCache();
        $inMemoryCache->save($namespace, $expectedValue, $key);

        $value = $inMemoryCache->getItem($namespace, $key);
        $this->assertSame($expectedValue, $value);

        $this->assertTrue($inMemoryCache->hasItem($namespace, $key));
        $inMemoryCache->deleteItem($namespace, $key);
        $this->assertFalse($inMemoryCache->hasItem($namespace, $key));
    }

    public function testEditValue(): void
    {
        $namespace = 'test';
        $key = 'first';
        $firstValue = 'value';
        $expectedValue = 'new value';

        $inMemoryCache = new InMemoryCache();
        $inMemoryCache->save($namespace, $firstValue, $key);

        $value = $inMemoryCache->getItem($namespace, $key);
        $this->assertSame($firstValue, $value);

        $inMemoryCache->save($namespace, $expectedValue, $key);
        $value = $inMemoryCache->getItem($namespace, $key);
        $this->assertSame($expectedValue, $value);
    }

    public function testValuesByNamespace(): void
    {
        $namespace = 'test';
        $key = 'first';
        $value = 'value';

        $expectedArray = [
            $key => $value,
        ];

        $inMemoryCache = new InMemoryCache();
        $inMemoryCache->save($namespace, $value, $key);

        $values = $inMemoryCache->getValuesByNamespace($namespace);
        $this->assertSame($expectedArray, $values);
    }

    public function testReset(): void
    {
        $namespace = 'test';
        $key = 'first';
        $value = 'value';

        $expectedArray = [
            $key => $value,
        ];

        $inMemoryCache = new InMemoryCache();
        $inMemoryCache->save($namespace, $value, $key);

        $values = $inMemoryCache->getValuesByNamespace($namespace);
        $this->assertSame($expectedArray, $values);

        $inMemoryCache->reset();

        $this->assertFalse($inMemoryCache->hasItem($namespace, $key));
    }

    public function testNullValueIsFoundAndNotComputedAgain(): void
    {
        $inMemoryCache = new InMemoryCache();
        $inMemoryCache->save('test', null, 'key');

        $this->assertTrue($inMemoryCache->hasItem('test', 'key'));
        $this->assertNull($inMemoryCache->getOrSaveValue('test', fn () => 'computed', 'key'));
    }

    public function testValueIsFoundUnderKeyAndNamespaceWithNotAllowedCharacters(): void
    {
        $inMemoryCache = new InMemoryCache();
        $inMemoryCache->save('App\\Model{namespace}', 'value', 'App\\Model\\Product', '15:1');

        $this->assertSame('value', $inMemoryCache->getItem('App\\Model{namespace}', 'App\\Model\\Product', '15:1'));
        $this->assertSame('value', $inMemoryCache->getItem('App~Model~namespace~', 'App~Model~Product~15~1'));
        $this->assertSame(
            ['App~Model~Product~15~1' => 'value'],
            $inMemoryCache->getValuesByNamespace('App\\Model{namespace}'),
        );
    }

    public function testMissingNamespaceAndMissingValueAreReportedByExceptions(): void
    {
        $inMemoryCache = new InMemoryCache();

        try {
            $inMemoryCache->getItem('test', 'key');
            $this->fail('Exception for a missing namespace was expected.');
        } catch (NamespaceCacheKeyNotFoundException) {
            $inMemoryCache->save('test', 'value', 'other key');
        }

        $this->expectException(ValueCacheKeyNotFoundException::class);

        $inMemoryCache->getItem('test', 'key');
    }

    public function testDeleteAllItemsInNamespace(): void
    {
        $namespace = 'test';

        $inMemoryCache = new InMemoryCache();
        $inMemoryCache->save($namespace, 'value-1', 1);
        $inMemoryCache->save($namespace, 'value-2', 2);

        $this->assertCount(2, $inMemoryCache->getValuesByNamespace($namespace));

        $inMemoryCache->deleteAllItemsInNamespace($namespace);

        $this->assertCount(0, $inMemoryCache->getValuesByNamespace($namespace));
    }
}
