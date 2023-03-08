import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { Translate } from 'next-translate';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

type ReturnType = { title: string | null; description: string | null };

export const getSeoTitleAndDescriptionForFriendlyUrlPage = (
    data: FriendlyUrlPageType,
    t: Translate,
    currentPage: number | null = null,
): ReturnType => {
    let title: string | null;
    let description: string | null = null;
    let totalCount: number | null = null;

    switch (data.__typename) {
        case 'Store':
            title = data.storeName;
            break;
        case 'ArticleSite':
            title = data.articleName;
            break;
        case 'Category':
            totalCount = data.products.totalCount;
            title = data.name;

            break;
        case 'BlogCategory':
            totalCount = data.articlesTotalCount;
            title = data.name;

            break;
        default:
            title = data.name;
    }

    if ('seoTitle' in data && data.seoTitle !== null) {
        title = data.seoTitle;
    }

    if ('seoMetaDescription' in data && data.seoMetaDescription !== null) {
        description = data.seoMetaDescription;
    }

    if (totalCount !== null && totalCount > DEFAULT_PAGE_SIZE) {
        const additionalPaginationText =
            ' ' +
            t('page {{ currentPage }} from {{ totalPages }}', {
                totalPages: Math.ceil(totalCount / DEFAULT_PAGE_SIZE),
                currentPage: currentPage,
            });
        title = title + additionalPaginationText;
    }

    return {
        title,
        description,
    };
};
