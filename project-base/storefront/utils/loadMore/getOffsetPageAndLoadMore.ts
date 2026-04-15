import { DEFAULT_PAGE_SIZE, PRODUCT_LIST_LIMIT } from 'config/constants';
import { calculatePageSize } from './calculatePageSize';

export const getOffsetPageAndLoadMore = (
    currentPage: number,
    currentLoadMore: number,
    pageSize = DEFAULT_PAGE_SIZE,
    productListLimit = PRODUCT_LIST_LIMIT,
) => {
    const loadedProductsDifference = calculatePageSize(currentLoadMore, pageSize) - productListLimit;

    if (loadedProductsDifference <= 0) {
        return undefined;
    }

    const pageOffset = Math.ceil(loadedProductsDifference / pageSize);

    return {
        updatedPage: currentPage + pageOffset,
        updatedLoadMore: currentLoadMore - pageOffset,
    };
};
