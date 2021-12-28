import { mapMainVariantDetailApiData, mapProductDetailApiData } from 'connectors/products/ProductDetail';
import { ArticleDetailType } from 'types/article';
import { BlogArticleDetailType } from 'types/blogArticle';
import { BlogCategoryType } from 'types/blogCategory';
import { BrandDetailType } from 'types/brand';
import { CategoryDetailType } from 'types/category';
import { FlagDetailType } from 'types/flag';
import { MainVariantDetailType } from 'types/product';
import { mapArticleDetailApiData } from 'connectors/article/ArticleDetail';
import { mapBlogArticleDetailApiData } from 'connectors/blogArticle/BlogArticle';
import { mapBlogCategoryData } from 'connectors/blogCategory/BlogCategory';
import { mapBrandDetailApiData } from 'connectors/brands/Brands';
import { mapCategoryDetailData } from 'connectors/categories/Categories';
import { mapFlagDetailApiData } from 'connectors/flags/Flags';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { mapStoreDetailApiData } from 'connectors/stores/StoreDetail';
import { ProductDetailType } from 'types/product';
import { StoreDetailType } from 'types/store';
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
