import { calculatePageSize } from './calculatePageSize';
import { DEFAULT_PAGE_SIZE } from 'config/constants';

export const hasReadAllItemsFromCache = (
    itemsFromCacheLength: number | undefined,
    currentLoadMore: number,
    currentPage: number,
    totalProductCount: number | undefined,
    pageSize = DEFAULT_PAGE_SIZE,
) => {
    if (!totalProductCount) {
        return false;
    }

    return (
        totalProductCount - (currentPage - 1) * pageSize === itemsFromCacheLength ||
        itemsFromCacheLength === calculatePageSize(currentLoadMore, pageSize)
    );
};
