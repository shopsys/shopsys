import { DEFAULT_PAGE_SIZE } from 'config/constants';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useCurrentLoadMoreQuery } from 'utils/queryParams/useCurrentLoadMoreQuery';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';

/**
 * Appends the current page information to a heading (document title or H1) of a paginated list
 */
export const useHeadingWithPagination = (
    heading: string | null | undefined,
    totalCount: number | undefined,
    pageSize: number = DEFAULT_PAGE_SIZE,
) => {
    const { t } = useTranslation();
    const currentPage = useCurrentPageQuery();
    const currentLoadMore = useCurrentLoadMoreQuery();

    if (!totalCount || totalCount <= pageSize) {
        return heading;
    }

    if (currentLoadMore > 0) {
        const totalPages = Math.ceil(totalCount / pageSize);
        return `${heading} ${t('page {{ currentPage }} to {{ currentPageWithLoadMore }} from {{ totalPages }}', {
            currentPage,
            totalPages,
            currentPageWithLoadMore: Math.min(currentPage + currentLoadMore, totalPages),
        })}`;
    }

    if (currentPage > 1) {
        return `${heading} ${t('page {{ currentPage }} from {{ totalPages }}', {
            currentPage,
            totalPages: Math.ceil(totalCount / pageSize),
        })}`;
    }

    return heading;
};
