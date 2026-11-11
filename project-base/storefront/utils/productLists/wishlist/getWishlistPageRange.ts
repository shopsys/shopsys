import { DEFAULT_PAGE_SIZE } from 'config/constants';

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
        offset: (page - 1) * DEFAULT_PAGE_SIZE,
    };
};
