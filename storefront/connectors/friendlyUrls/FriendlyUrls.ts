import { mapArticleDetail } from 'connectors/articleInterface/article/Article';
import { mapBlogArticleDetail } from 'connectors/articleInterface/blogArticle/BlogArticle';
import { mapBrandDetail } from 'connectors/brands/Brands';
import { mapCategoryDetailData } from 'connectors/categories/Categories';
import { mapFlagDetailApiData } from 'connectors/flags/Flags';
import { mapMainVariantDetailApiData, mapProductDetailApiData } from 'connectors/products/ProductDetail';
import { mapStoreDetailApiData } from 'connectors/stores/StoreDetail';
import { Maybe, useSlugQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { FILTER_QUERY_PARAMETER_NAME, SORT_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export function useFriendlyUrlResolvedData(slug: string): { data: Maybe<FriendlyUrlPageType>; fetching: boolean } {
    const router = useRouter();
    const categoryDetailSort = getProductListSort(
        parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]),
    );
    const categoryParametersFilter = getFilterOptions(
        parseFilterOptionsFromQuery(router.query[FILTER_QUERY_PARAMETER_NAME]),
    );
    const [{ data, error, fetching }] = useSlugQueryApi({
        variables: {
            slug,
            orderingMode: categoryDetailSort,
            filter: mapParametersFilter(categoryParametersFilter),
        },
    });

    useQueryError(error);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    if (data?.slug?.__typename === undefined) {
        return { data: null, fetching };
    }

    switch (data.slug.__typename) {
        case 'RegularProduct':
            return { data: mapProductDetailApiData(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'MainVariant':
            return { data: mapMainVariantDetailApiData(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'Category':
            return { data: mapCategoryDetailData(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'Store':
            return { data: mapStoreDetailApiData(data.slug), fetching };
        case 'ArticleSite':
            return { data: mapArticleDetail(data.slug), fetching };
        case 'BlogArticle':
            return { data: mapBlogArticleDetail(data.slug, currentDomainConfig), fetching };
        case 'Brand':
            return { data: mapBrandDetail(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'Flag':
            return { data: mapFlagDetailApiData(data.slug, currentDomainConfig.currencyCode), fetching };
        case 'BlogCategory':
            return { data: data.slug, fetching };
        default:
            return { data: null, fetching };
    }
}
