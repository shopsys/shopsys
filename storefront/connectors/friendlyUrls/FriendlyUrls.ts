import { ArticleDetailType } from 'connectors/article/types';
import { CategoryDetailType } from 'components/Pages/CategoryDetail/types';
import { mapArticleDetailApiData } from 'connectors/article/ArticleDetail';
import { mapCategoryDetailData } from 'connectors/categories/Categories';
import { mapProductDetailApiData } from 'connectors/products/ProductDetail';
import { mapStoreDetailApiData } from 'connectors/stores/StoreDetail';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import { StoreDetailType } from 'connectors/stores/types';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';
import { useSlugQueryApi } from 'graphql/generated';

export function getFriendlyUrlResolvedData(
    slug: string,
): ProductDetailType | CategoryDetailType | StoreDetailType | ArticleDetailType | undefined | null {
    const categoryDetailSort = useShopsysSelector((state) => state.user.sort);
    const pagination = useShopsysSelector((state) => state.user.pagination);
    const [{ data, error }] = useSlugQueryApi({
        variables: {
            slug,
            sortingMode: categoryDetailSort,
            endCursorForPagination: pagination.paginationCursor,
            pageSize: pagination.pageSize,
        },
    });
    useQueryError(error);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    if (data?.slug === null || data?.slug === undefined) {
        return undefined;
    }

    if (
        data.slug.__typename === 'RegularProduct' ||
        data.slug.__typename === 'MainVariant' ||
        data.slug.__typename === 'Variant'
    ) {
        return mapProductDetailApiData(data.slug, currentDomainConfig.currencyCode);
    } else if (data.slug.__typename === 'Category') {
        return mapCategoryDetailData(data.slug, currentDomainConfig.currencyCode);
    } else if (data.slug.__typename === 'Store') {
        return mapStoreDetailApiData(data.slug);
    } else if (data.slug.__typename === 'Article') {
        return mapArticleDetailApiData(data.slug);
    }

    return undefined;
}
