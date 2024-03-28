import { getCategoryOrSeoCategoryGtmPageType } from './getCategoryOrSeoCategoryGtmPageType';
import { getGtmPageInfoType } from './getGtmPageInfoType';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { TypeBrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import {
    GtmPageInfoType,
    GtmPageInfoInterface,
    GtmCategoryDetailPageInfoType,
    GtmBlogArticleDetailPageInfoType,
    GtmBrandDetailPageInfoType,
} from 'gtm/types/objects';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export const getGtmPageInfoTypeForFriendlyUrl = (
    friendlyUrlPageData: FriendlyUrlPageType | null | undefined,
): GtmPageInfoType => {
    let pageInfo = getGtmPageInfoType(GtmPageType.not_found, friendlyUrlPageData?.breadcrumb);

    if (friendlyUrlPageData === undefined) {
        return pageInfo;
    }

    switch (friendlyUrlPageData?.__typename) {
        case 'RegularProduct':
        case 'MainVariant':
            pageInfo.type = GtmPageType.product_detail;
            break;
        case 'Category':
            pageInfo = getPageInfoForCategoryDetailPage(pageInfo, friendlyUrlPageData);
            break;
        case 'Store':
            pageInfo.type = GtmPageType.store_detail;
            break;
        case 'ArticleSite':
            pageInfo.type = GtmPageType.article_detail;
            break;
        case 'BlogArticle':
            pageInfo = getPageInfoForBlogArticleDetailPage(pageInfo, friendlyUrlPageData);
            break;
        case 'BlogCategory':
            pageInfo.type = GtmPageType.blog_category_detail;
            break;
        case 'Flag':
            pageInfo.type = GtmPageType.flag_detail;
            break;
        case 'Brand':
            pageInfo = getPageInfoForBrandDetailPage(pageInfo, friendlyUrlPageData);
            break;
        default:
            break;
    }

    return pageInfo;
};

const getPageInfoForCategoryDetailPage = (
    defaultPageInfo: GtmPageInfoInterface,
    categoryDetailData: TypeCategoryDetailFragment,
): GtmCategoryDetailPageInfoType => ({
    ...defaultPageInfo,
    type: getCategoryOrSeoCategoryGtmPageType(categoryDetailData.originalCategorySlug),
    category: categoryDetailData.breadcrumb.map(({ name }) => name),
    categoryId: categoryDetailData.categoryHierarchy.map(({ id }) => id),
});

const getPageInfoForBlogArticleDetailPage = (
    defaultPageInfo: GtmPageInfoType,
    blogArticleDetailData: TypeBlogArticleDetailFragment,
): GtmBlogArticleDetailPageInfoType => ({
    ...defaultPageInfo,
    type: GtmPageType.blog_article_detail,
    articleId: blogArticleDetailData.id,
});

const getPageInfoForBrandDetailPage = (
    defaultPageInfo: GtmPageInfoType,
    brandDetailData: TypeBrandDetailFragment,
): GtmBrandDetailPageInfoType => ({
    ...defaultPageInfo,
    type: GtmPageType.brand_detail,
    brandId: brandDetailData.id,
});
