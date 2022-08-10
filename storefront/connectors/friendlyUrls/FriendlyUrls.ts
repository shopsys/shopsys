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

export function useFriendlyUrlResolvedData(slug: string): { data: Maybe<FriendlyUrlPageType>; fetching: boolean } {
    const router = useRouter();
    const categoryDetailSort = getProductListSort(parseProductListSortFromQuery(router.query.sort));
    const pagination = useShopsysSelector((state) => state.user.pagination);
    const categoryParametersFilter = getFilterOptions(parseFilterOptionsFromQuery(router.query.filter));
    const [{ data, error, fetching }] = useSlugQueryApi({
        variables: {
            slug,
            orderingMode: categoryDetailSort,
            endCursorForPagination: pagination.paginationCursor,
            pageSize: pagination.pageSize,
            filter: mapParametersFilter(categoryParametersFilter),
        },
        requestPolicy: 'network-only',
    });

    useQueryError(error);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    if (data?.slug?.__typename === undefined) {
        return { data: null, fetching };
    }

    switch (data.slug.__typename) {
        case 'RegularProduct':
        case 'Variant':
            return { data: mapProductDetailApiData(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'MainVariant':
            return { data: mapMainVariantDetailApiData(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'Category':
            return { data: mapCategoryDetailData(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'Store':
            return { data: mapStoreDetailApiData(data.slug), fetching };
        case 'Article':
            return { data: mapArticleDetail(data.slug), fetching };
        case 'BlogArticle':
            return { data: mapBlogArticleDetail(data.slug, currentDomainConfig), fetching };
        case 'Brand':
            return { data: mapBrandDetail(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'Flag':
            return { data: mapFlagDetailApiData(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'BlogCategory':
            return { data: mapBlogCategoryDetail(data.slug), fetching };
        default:
            return { data: null, fetching };
    }
}
