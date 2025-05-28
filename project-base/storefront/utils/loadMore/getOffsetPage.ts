export const getOffsetPage = (currentPage: number, currentLoadMore: number) => {
    const offsetPage = currentPage + currentLoadMore;

    if (offsetPage === currentPage) {
        return undefined;
    }

    return {
        updatedPage: offsetPage,
        updatedLoadMore: 0,
    };
};
