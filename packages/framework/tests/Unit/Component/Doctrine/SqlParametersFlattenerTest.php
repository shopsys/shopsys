<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Doctrine;

use Iterator;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlParametersFlattener;

class SqlParametersFlattenerTest extends TestCase
{
    /**
     * @dataProvider expandArrayParametersDataProvider
     * @param string $dql
     * @param array $parameters
     * @param array $expectedFlattenedParameters
     */
    public function testExpandArrayParameters(string $dql, array $parameters, array $expectedFlattenedParameters): void
    {
        $sqlParametersFlattener = new SqlParametersFlattener();
        $actualFlattenedParameters = $sqlParametersFlattener::flattenArrayParameters($dql, $parameters);

        $this->assertSame($expectedFlattenedParameters, $actualFlattenedParameters);
    }

    /**
     * @return \Iterator
     */
    public function expandArrayParametersDataProvider(): Iterator
    {
        yield [
            'dql' => 'SELECT a FROM Article WHERE id = :id AND name = :name',
            'parameters' => [
                'id' => 1,
                'name' => 'name',
            ],
            'expectedFlattenedParameters' => [1, 'name'],
        ];

        yield [
            'dql' => 'SELECT a FROM Article WHERE id = :id AND name = :name',
            'parameters' => [
                'name' => 'name',
                'id' => 1,
            ],
            'expectedFlattenedParameters' => [1, 'name'],
        ];
    }
}
