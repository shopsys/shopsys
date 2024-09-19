import { calculatePageSize } from './calculatePageSize';
import { DEFAULT_PAGE_SIZE } from 'config/constants';

const PRODUCT_LIST_LIMIT = 310;

export const getOffsetPageAndLoadMore = (
    currentPage: number,
    currentLoadMore: number,
    pageSize = DEFAULT_PAGE_SIZE,
) => {
    const loadedProductsDifference = calculatePageSize(currentLoadMore, pageSize) - PRODUCT_LIST_LIMIT;

    if (loadedProductsDifference <= 0) {
        return undefined;
    }

    const pageOffset = Math.ceil(loadedProductsDifference / pageSize);

    return {
        updatedPage: currentPage + pageOffset,
        updatedLoadMore: currentLoadMore - pageOffset,
    };
};
