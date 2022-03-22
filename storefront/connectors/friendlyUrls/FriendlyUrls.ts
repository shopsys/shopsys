import { mapMainVariantDetailApiData, mapProductDetailApiData } from 'connectors/products/ProductDetail';
import { ArticleDetailType } from 'types/article';
import { BlogArticleDetailType } from 'types/blogArticle';
import { BlogCategoryDetailType } from 'types/blogCategory';
import { BrandDetailType } from 'types/brand';
import { CategoryDetailType } from 'types/category';
import { FlagDetailType } from 'types/flag';
import { MainVariantDetailType } from 'types/product';
import { mapArticleDetail } from 'connectors/articleInterface/article/Article';
import { mapBlogArticleDetail } from 'connectors/articleInterface/blogArticle/BlogArticle';
import { mapBlogCategoryDetail } from 'connectors/blogCategory/BlogCategory';
import { mapBrandDetail } from 'connectors/brands/Brands';
import { mapCategoryDetailData } from 'connectors/categories/Categories';
import { mapFlagDetailApiData } from 'connectors/flags/Flags';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { mapStoreDetailApiData } from 'connectors/stores/StoreDetail';
import { ProductDetailType } from 'types/product';
import { StoreDetailType } from 'types/store';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';
import { useSlugQueryApi } from 'graphql/generated';

export function useFriendlyUrlResolvedData(
    slug: string,
):
    | ProductDetailType
    | MainVariantDetailType
    | CategoryDetailType
    | StoreDetailType
    | ArticleDetailType
    | BlogArticleDetailType
    | BlogCategoryDetailType
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
            return undefined;
    }
}
