import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useQueryParams } from 'hooks/useQueryParams';

export const useSeoTitleWithPagination = (
    totalCount: number | undefined,
    name: string | null | undefined,
    seoTitle?: string | null | undefined,
) => {
    const t = useTypedTranslationFunction();
    const { currentPage } = useQueryParams();
    let title = seoTitle || name;

    if (totalCount && totalCount > DEFAULT_PAGE_SIZE) {
        title = `${title} ${t('page {{ currentPage }} from {{ totalPages }}', {
            currentPage,
            totalPages: Math.ceil(totalCount / DEFAULT_PAGE_SIZE),
        })}`;
    }

    return title;
};
