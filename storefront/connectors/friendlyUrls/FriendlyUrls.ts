import { mapArticleDetail } from 'connectors/articleInterface/article/Article';
import { mapBlogArticleDetail } from 'connectors/articleInterface/blogArticle/BlogArticle';
import { mapBlogCategoryDetail } from 'connectors/blogCategory/BlogCategory';
import { mapBrandDetail } from 'connectors/brands/Brands';
import { mapCategoryDetailData } from 'connectors/categories/Categories';
import { mapFlagDetailApiData } from 'connectors/flags/Flags';
import { mapMainVariantDetailApiData, mapProductDetailApiData } from 'connectors/products/ProductDetail';
import { mapStoreDetailApiData } from 'connectors/stores/StoreDetail';
import { Maybe, useSlugQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/GetFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/ParseFilterOptionsFromQuery';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export function useFriendlyUrlResolvedData(slug: string): Maybe<FriendlyUrlPageType> {
    const router = useRouter();
    const categoryDetailSort = getProductListSort(parseProductListSortFromQuery(router.query.sort));
    const pagination = useShopsysSelector((state) => state.user.pagination);
    const categoryParametersFilter = getFilterOptions(parseFilterOptionsFromQuery(router.query.filter));
    const [{ data, error }] = useSlugQueryApi({
        requestPolicy: 'network-only',
        variables: {
            slug,
            orderingMode: categoryDetailSort,
            endCursorForPagination: pagination.paginationCursor,
            pageSize: pagination.pageSize,
            filter: mapParametersFilter(categoryParametersFilter),
        },
    });

    useQueryError(error);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    if (data?.slug?.__typename === undefined) {
        return null;
    }

    switch (data.slug.__typename) {
        case 'RegularProduct':
        case 'Variant':
            return mapProductDetailApiData(data.slug, currentDomainConfig.currencyCode);
        case 'MainVariant':
            return mapMainVariantDetailApiData(data.slug, currentDomainConfig.currencyCode);
        case 'Category':
            return mapCategoryDetailData(data.slug, currentDomainConfig.currencyCode);
        case 'Store':
            return mapStoreDetailApiData(data.slug);
        case 'Article':
            return mapArticleDetail(data.slug);
        case 'BlogArticle':
            return mapBlogArticleDetail(data.slug, currentDomainConfig);
        case 'Brand':
            return mapBrandDetail(data.slug, currentDomainConfig.currencyCode);
        case 'Flag':
            return mapFlagDetailApiData(data.slug, currentDomainConfig.currencyCode);
        case 'BlogCategory':
            return mapBlogCategoryDetail(data.slug);
        default:
            return null;
    }
}
