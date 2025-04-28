'use client';

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
        // Calculate basic pagination values
        const lastPage = Math.ceil(totalCount / pageSize);
        const firstPage = 1;

        // Define how many page numbers we want to show
        const totalDesktopPageNumbers = 7;
        const totalMobilePageNumbers = totalDesktopPageNumbers - 2;
        const maxVisiblePages = isMobilePaginationVisible ? totalMobilePageNumbers : totalDesktopPageNumbers;

        // If we have fewer pages than our display limit, show all pages
        // Example: For 5 total pages on desktop (max 7), show: 1 2 3 4 5
        if (lastPage <= maxVisiblePages) {
            return range(1, lastPage);
        }

        // Define where pagination patterns change
        const leftBreakpoint = isMobilePaginationVisible ? 3 : 4;
        const rightBreakpoint = isMobilePaginationVisible ? lastPage - 2 : lastPage - 3;

        // Handle pages near the start
        // Mobile example: 1 2 3 4 ... 10
        // Desktop example: 1 2 3 4 5 ... 10
        if (currentPage < leftBreakpoint) {
            const startRange = range(firstPage, leftBreakpoint + (isMobilePaginationVisible ? 1 : 2));
            return [...startRange, lastPage];
        }

        // Handle pages near the end
        // Mobile example: 1 ... 7 8 9 10
        // Desktop example: 1 ... 6 7 8 9 10
        if (currentPage >= rightBreakpoint) {
            const endRange = range(
                lastPage - (isMobilePaginationVisible ? leftBreakpoint : leftBreakpoint + 1),
                lastPage,
            );
            return [firstPage, ...endRange];
        }

        // Handle middle pages
        // Example: 1 ... 4 5 6 ... 10
        if (currentPage >= leftBreakpoint - 1 && currentPage < rightBreakpoint) {
            const middleRange = range(currentPage - 1, currentPage + 1);
            return [firstPage, ...middleRange, lastPage];
        }

        return null;
    }, [totalCount, currentPage, isMobilePaginationVisible, pageSize]);
