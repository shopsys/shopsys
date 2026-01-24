<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Paginator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class PaginationResultTest extends TestCase
{
    public static function getTestPageCountData(): array
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

    #[DataProvider('getTestPageCountData')]
    public function testGetPageCount(
        mixed $page,
        mixed $pageSize,
        mixed $totalCount,
        mixed $results,
        mixed $expectedPageCount,
    ): void {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, $results);

        $this->assertSame($expectedPageCount, $paginationResult->getPageCount());
    }

    public static function getTestIsFirstPageData(): iterable
    {
        yield [1, 10, 20, true];

        yield [2, 10, 20, false];

        yield [1, null, 20, true];
    }

    #[DataProvider('getTestIsFirstPageData')]
    public function testIsFirstPage(int $page, ?int $pageSize, int $totalCount, bool $expectedIsFirst): void
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedIsFirst, $paginationResult->isFirstPage());
    }

    public static function getTestIsLastPageData(): iterable
    {
        yield [1, 10, 20, false];

        yield [2, 10, 20, true];

        yield [1, 10, 21, false];

        yield [2, 10, 21, false];

        yield [3, 10, 21, true];

        yield [1, null, 20, true];
    }

    #[DataProvider('getTestIsLastPageData')]
    public function testIsLastPage(int $page, ?int $pageSize, int $totalCount, bool $expectedIsLast): void
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedIsLast, $paginationResult->isLastPage());
    }

    public static function getTestGetPreviousPageData(): iterable
    {
        yield [1, 10, 20, null];

        yield [2, 10, 20, 1];

        yield [3, 10, 21, 2];

        yield [1, null, 20, null];
    }

    #[DataProvider('getTestGetPreviousPageData')]
    public function testGetPreviousPage(int $page, ?int $pageSize, int $totalCount, ?int $expectedPrevious): void
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedPrevious, $paginationResult->getPreviousPage());
    }

    public static function getTestGetNextPageData(): iterable
    {
        yield [1, 10, 20, 2];

        yield [2, 10, 20, null];

        yield [2, 10, 21, 3];

        yield [3, 10, 21, null];

        yield [1, null, 20, null];
    }

    #[DataProvider('getTestGetNextPageData')]
    public function testGetNextPage(int $page, ?int $pageSize, int $totalCount, ?int $expectedNext): void
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedNext, $paginationResult->getNextPage());
    }
}
