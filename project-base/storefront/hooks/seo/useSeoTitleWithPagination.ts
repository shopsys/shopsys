import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';

export const useSeoTitleWithPagination = (
    totalCount: number | undefined,
    name: string | null | undefined,
    seoTitle?: string | null | undefined,
) => {
    const { t } = useTranslation();
    const { currentPage, currentLoadMore } = useQueryParams();
    const title = seoTitle || name;

    if (!totalCount || totalCount < DEFAULT_PAGE_SIZE) {
        return title;
    }

    if (currentLoadMore > 0) {
        const totalPages = Math.ceil(totalCount / DEFAULT_PAGE_SIZE);
        return `${title} ${t('page {{ currentPage }} to {{ currentPageWithLoadMore }} from {{ totalPages }}', {
            currentPage,
            totalPages,
            currentPageWithLoadMore: Math.min(currentPage + currentLoadMore, totalPages),
        })}`;
    }

    return `${title} ${t('page {{ currentPage }} from {{ totalPages }}', {
        currentPage,
        totalPages: Math.ceil(totalCount / DEFAULT_PAGE_SIZE),
    })}`;
};
