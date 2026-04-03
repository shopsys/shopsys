import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { calculatePageSize } from './calculatePageSize';

export const getPageSizeInfo = (
    readProductsFromCache: boolean,
    currentLoadMore: number,
    pageSize = DEFAULT_PAGE_SIZE,
) => {
    if (readProductsFromCache) {
        return { pageSize, isMoreThanOnePage: false };
    }

    return { pageSize: calculatePageSize(currentLoadMore, pageSize), isMoreThanOnePage: true };
};
