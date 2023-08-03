export const parseLoadMoreFromQuery = (query: string | string[] | undefined): number => {
    const parsedLoadMore = Number(query);
    return isNaN(parsedLoadMore) ? 0 : parsedLoadMore;
};
