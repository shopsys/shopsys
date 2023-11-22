<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Paginator;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class PaginationResultTest extends TestCase
{
    /**
     * @return int[][]|never[][][]|null[][]
     */
    public function getTestPageCountData(): array
    {
        return [
            [1, 10, 40, [], 4],
            [1, 10, 41, [], 5],
            [1, 10, 49, [], 5],
            [1, 10, 50, [], 5],
            [1, 10, 51, [], 6],
            [1, 10, 5, [], 1],
            [1, 0, 0, [], 0],
            [1, null, 5, [], 1],
            [1, null, 0, [], 0],
        ];
    }

    /**
     * @dataProvider getTestPageCountData
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param mixed[] $results
     * @param int $expectedPageCount
     */
    public function testGetPageCount(int $page, ?int $pageSize, int $totalCount, array $results, int $expectedPageCount): void
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, $results);

        $this->assertSame($expectedPageCount, $paginationResult->getPageCount());
    }

    public function getTestIsFirstPageData()
    {
        yield [1, 10, 20, true];

        yield [2, 10, 20, false];

        yield [1, null, 20, true];
    }

    /**
     * @dataProvider getTestIsFirstPageData
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param bool $expectedIsFirst
     */
    public function testIsFirstPage(int $page, ?int $pageSize, int $totalCount, bool $expectedIsFirst): void
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedIsFirst, $paginationResult->isFirstPage());
    }

    public function getTestIsLastPageData()
    {
        yield [1, 10, 20, false];

        yield [2, 10, 20, true];

        yield [1, 10, 21, false];

        yield [2, 10, 21, false];

        yield [3, 10, 21, true];

        yield [1, null, 20, true];
    }

    /**
     * @dataProvider getTestIsLastPageData
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param bool $expectedIsLast
     */
    public function testIsLastPage(int $page, ?int $pageSize, int $totalCount, bool $expectedIsLast): void
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedIsLast, $paginationResult->isLastPage());
    }

    public function getTestGetPreviousPageData()
    {
        yield [1, 10, 20, null];

        yield [2, 10, 20, 1];

        yield [3, 10, 21, 2];

        yield [1, null, 20, null];
    }

    /**
     * @dataProvider getTestGetPreviousPageData
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param int|null $expectedPrevious
     */
    public function testGetPreviousPage(int $page, ?int $pageSize, int $totalCount, ?int $expectedPrevious): void
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedPrevious, $paginationResult->getPreviousPage());
    }

    public function getTestGetNextPageData()
    {
        yield [1, 10, 20, 2];

        yield [2, 10, 20, null];

        yield [2, 10, 21, 3];

        yield [3, 10, 21, null];

        yield [1, null, 20, null];
    }

    /**
     * @dataProvider getTestGetNextPageData
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param int|null $expectedNext
     */
    public function testGetNextPage(int $page, ?int $pageSize, int $totalCount, ?int $expectedNext): void
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedNext, $paginationResult->getNextPage());
    }
}
