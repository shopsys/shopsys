import { useMemo } from 'react';

const range = (start: number, end: number) => {
    const rangeArray = [];
    for (let i = start; i <= end; i++) {
        rangeArray.push(i);
    }
    return rangeArray;
};

export const usePagination = (
    totalCount: number,
    currentPage: number,
    isMobilePaginationVisible: boolean,
    pageSize: number,
): number[] | null =>
    useMemo(() => {
        const lastPage = Math.ceil(totalCount / pageSize);
        const firstPage = 1;
        const totalPageNumbers = 7;
        const totalMobilePageNumbers = totalPageNumbers - 2;
        if (
            (isMobilePaginationVisible && totalMobilePageNumbers >= lastPage) ||
            (!isMobilePaginationVisible && totalPageNumbers >= lastPage)
        ) {
            return range(1, lastPage);
        }
        const leftPaginationBreakpoint = 3;
        const secondToLastPage = lastPage - 2;
        const thirdToLastPage = lastPage - 3;

        if (isMobilePaginationVisible && currentPage < leftPaginationBreakpoint && currentPage < secondToLastPage) {
            const firstNumbersOfPagination = range(firstPage, leftPaginationBreakpoint + 1);
            return [...firstNumbersOfPagination, lastPage];
        } else if (
            !isMobilePaginationVisible &&
            currentPage < leftPaginationBreakpoint + 2 &&
            currentPage < thirdToLastPage
        ) {
            const firstNumbersOfPagination = range(firstPage, leftPaginationBreakpoint + 2);
            return [...firstNumbersOfPagination, lastPage];
        }

        if (isMobilePaginationVisible && currentPage > leftPaginationBreakpoint && currentPage >= secondToLastPage) {
            const lastNumbersOfPagination = range(lastPage - leftPaginationBreakpoint, lastPage);
            return [firstPage, ...lastNumbersOfPagination];
        } else if (
            !isMobilePaginationVisible &&
            currentPage > leftPaginationBreakpoint + 1 &&
            currentPage >= thirdToLastPage
        ) {
            const lastNumbersOfPagination = range(lastPage - leftPaginationBreakpoint - 1, lastPage);
            return [firstPage, ...lastNumbersOfPagination];
        }

        if (
            (isMobilePaginationVisible &&
                currentPage > leftPaginationBreakpoint - 1 &&
                currentPage < secondToLastPage) ||
            (!isMobilePaginationVisible && currentPage > leftPaginationBreakpoint && currentPage < thirdToLastPage)
        ) {
            const middleRange = range(currentPage - 1, currentPage + 1);
            if (isMobilePaginationVisible) {
                return [firstPage, ...middleRange, lastPage];
            }
            return [firstPage, ...middleRange, lastPage];
        }

        return null;
    }, [totalCount, currentPage, isMobilePaginationVisible, pageSize]);
