import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { calculatePageSize } from 'utils/loadMore/calculatePageSize';

export const getWishlistPageRange = (requestedPage: number, requestedLoadMore: number, totalCount?: number) => {
    const validPage = Number.isSafeInteger(requestedPage) && requestedPage > 0 ? requestedPage : 1;
    const page =
        totalCount === undefined
            ? validPage
            : Math.min(validPage, Math.max(1, Math.ceil(totalCount / DEFAULT_PAGE_SIZE)));
    const loadMore = Number.isSafeInteger(requestedLoadMore) && requestedLoadMore >= 0 ? requestedLoadMore : 0;
    return {
        page,
        loadMore,
        offset: Math.min(2_147_483_647, (page - 1) * DEFAULT_PAGE_SIZE),
        limit: Math.min(2_147_483_647, calculatePageSize(loadMore)),
    };
};
