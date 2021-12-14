import { mapCategoryDetailData, mapParametersFilter } from 'connectors/categories/Categories';
import { mapMainVariantDetailApiData, mapProductDetailApiData } from 'connectors/products/ProductDetail';
import { ArticleDetailType } from 'types/article';
import { BlogArticleDetailType } from 'components/Pages/BlogArticle/types';
import { BlogCategoryType } from 'connectors/blogCategory/types';
import { BrandDetailType } from 'connectors/brands/types';
import { CategoryDetailType } from 'components/Pages/CategoryDetail/types';
import { FlagDetailType } from 'connectors/flags/types';
import { MainVariantDetailType } from 'connectors/products/types';
import { mapArticleDetailApiData } from 'connectors/article/ArticleDetail';
import { mapBlogArticleDetailApiData } from 'connectors/blogArticle/BlogArticle';
import { mapBlogCategoryData } from 'connectors/blogCategory/BlogCategory';
import { mapBrandDetailApiData } from 'connectors/brands/Brands';
import { mapFlagDetailApiData } from 'connectors/flags/Flags';
import { mapStoreDetailApiData } from 'connectors/stores/StoreDetail';
import { ProductDetailType } from 'components/Pages/ProductDetail/types';
import { StoreDetailType } from 'connectors/stores/types';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';
import { useSlugQueryApi } from 'graphql/generated';

export function getFriendlyUrlResolvedData(
    slug: string,
):
    | ProductDetailType
    | MainVariantDetailType
    | CategoryDetailType
    | StoreDetailType
    | ArticleDetailType
    | BlogArticleDetailType
    | BlogCategoryType
    | BrandDetailType
    | FlagDetailType
    | undefined
    | null {
    const categoryDetailSort = useShopsysSelector((state) => state.user.sort);
    const pagination = useShopsysSelector((state) => state.user.pagination);
    const categoryParametersFilter = useShopsysSelector((state) => state.optionsFilter);
    const [{ data, error }] = useSlugQueryApi({
        variables: {
            slug,
            sortingMode: categoryDetailSort,
            endCursorForPagination: pagination.paginationCursor,
            pageSize: pagination.pageSize,
            filter: mapParametersFilter(categoryParametersFilter),
        },
    });
    useQueryError(error);
    const currentDomainConfig = useShopsysSelector((state) => state.domain);

    if (data?.slug === null || data?.slug === undefined) {
        return undefined;
    }

    if (data.slug.__typename === 'RegularProduct' || data.slug.__typename === 'Variant') {
        return mapProductDetailApiData(data.slug, currentDomainConfig.currencyCode);
    } else if (data.slug.__typename === 'MainVariant') {
        return mapMainVariantDetailApiData(data.slug, currentDomainConfig.currencyCode);
    } else if (data.slug.__typename === 'Category') {
        return mapCategoryDetailData(data.slug, currentDomainConfig.currencyCode);
    } else if (data.slug.__typename === 'Store') {
        return mapStoreDetailApiData(data.slug);
    } else if (data.slug.__typename === 'Article') {
        return mapArticleDetailApiData(data.slug);
    } else if (data.slug.__typename === 'BlogArticle') {
        return mapBlogArticleDetailApiData(data.slug, currentDomainConfig);
    } else if (data.slug.__typename === 'Brand') {
        return mapBrandDetailApiData(data.slug, currentDomainConfig.currencyCode);
    } else if (data.slug.__typename === 'Flag') {
        return mapFlagDetailApiData(data.slug, currentDomainConfig.currencyCode);
    } else if (data.slug.__typename === 'BlogCategory') {
        return mapBlogCategoryData(data.slug);
    }

    return undefined;
}
