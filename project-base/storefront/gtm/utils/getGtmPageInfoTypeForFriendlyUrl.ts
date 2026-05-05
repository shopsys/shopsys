import { TypeArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { TypeBrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { TypeMainVariantDetailFragment } from 'graphql/requests/products/fragments/MainVariantDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import {
    GtmArticleDetailPageInfoType,
    GtmBlogArticleDetailPageInfoType,
    GtmBrandDetailPageInfoType,
    GtmCategoryDetailPageInfoType,
    GtmPageInfoInterface,
    GtmPageInfoType,
    GtmProductDetailPageInfoType,
} from 'gtm/types/objects';
import { getSpecialArticleGtmType } from 'gtm/utils/getSpecialArticleGtmTypes';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import { getCategoryOrSeoCategoryGtmPageType } from './getCategoryOrSeoCategoryGtmPageType';
import { getGtmPageInfoType } from './getGtmPageInfoType';

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
            pageInfo = getPageInfoForProductDetailPage(pageInfo, friendlyUrlPageData);
            break;
        case 'Category':
            pageInfo = getPageInfoForCategoryDetailPage(pageInfo, friendlyUrlPageData);
            break;
        case 'Store':
            pageInfo.type = GtmPageType.store_detail;
            break;
        case 'ArticleSite': {
            pageInfo = getPageInfoForArticleDetailPage(pageInfo, friendlyUrlPageData);
            break;
        }
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

const getPageInfoForProductDetailPage = (
    defaultPageInfo: GtmPageInfoInterface,
    productDetailData: TypeProductDetailFragment | TypeMainVariantDetailFragment,
): GtmProductDetailPageInfoType => ({
    ...defaultPageInfo,
    type: isSoldOutProductDetailPage(productDetailData) ? GtmPageType.product_sold_out : GtmPageType.product_detail,
});

const isSoldOutProductDetailPage = (
    productDetailData: TypeProductDetailFragment | TypeMainVariantDetailFragment,
): boolean =>
    productDetailData.isSellingDenied ||
    (!productDetailData.isInquiryType &&
        (productDetailData.availability.status === TypeAvailabilityStatusEnum.OutOfStock ||
            productDetailData.isCurrentlyOutOfStock));

const getPageInfoForArticleDetailPage = (
    defaultPageInfo: GtmPageInfoType,
    articleDetailData: TypeArticleDetailFragment,
): GtmArticleDetailPageInfoType => ({
    ...defaultPageInfo,
    type: getSpecialArticleGtmType(articleDetailData.slug) ?? GtmPageType.article_detail,
    articleId: articleDetailData.uuid,
});

const getPageInfoForBlogArticleDetailPage = (
    defaultPageInfo: GtmPageInfoType,
    blogArticleDetailData: TypeBlogArticleDetailFragment,
): GtmBlogArticleDetailPageInfoType => ({
    ...defaultPageInfo,
    type: GtmPageType.blog_article_detail,
    articleId: blogArticleDetailData.uuid,
});

const getPageInfoForBrandDetailPage = (
    defaultPageInfo: GtmPageInfoType,
    brandDetailData: TypeBrandDetailFragment,
): GtmBrandDetailPageInfoType => ({
    ...defaultPageInfo,
    type: GtmPageType.brand_detail,
    brandId: brandDetailData.id,
});
