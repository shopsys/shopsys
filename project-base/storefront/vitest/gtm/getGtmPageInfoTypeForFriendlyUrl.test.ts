import { TypeArticleDetailFragment } from 'graphql/requests/articlesInterface/articles/fragments/ArticleDetailFragment.generated';
import { TypeBlogArticleDetailFragment } from 'graphql/requests/articlesInterface/blogArticles/fragments/BlogArticleDetailFragment.generated';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { getGtmPageInfoTypeForFriendlyUrl } from 'gtm/utils/getGtmPageInfoTypeForFriendlyUrl';
import { describe, expect, test } from 'vitest';

const articleDetail = {
    __typename: 'ArticleSite',
    uuid: '4ed28a90-3570-41f1-a6ba-0f81dca66dc4',
    slug: '/test-article',
    placement: 'footer',
    articleName: 'Test article',
    text: null,
    breadcrumb: [],
    seoTitle: null,
    seoMetaDescription: null,
    createdAt: '2026-04-30T10:00:00+02:00',
    seoH1: null,
} satisfies TypeArticleDetailFragment;

const productDetail = {
    __typename: 'RegularProduct',
    breadcrumb: [],
    availability: {
        __typename: 'Availability',
        name: 'In stock',
        status: TypeAvailabilityStatusEnum.InStock,
    },
    isSellingDenied: false,
    isCurrentlyOutOfStock: false,
    isInquiryType: false,
} as unknown as TypeProductDetailFragment;

const blogArticleDetail = {
    __typename: 'BlogArticle',
    id: 123,
    uuid: 'a1c1d337-b707-4e7e-9a9a-6875e1f5e708',
    name: 'Test blog article',
    slug: '/test-blog-article',
    link: '/blog/test-blog-article',
    text: null,
    publishDate: '2026-04-30T10:00:00+02:00',
    status: 'published',
    seoTitle: null,
    seoMetaDescription: null,
    seoH1: null,
    mainBlogCategoryUuid: 'f2a0464a-b2d3-4d8c-8326-45d1f0a5db29',
    mainImage: null,
    breadcrumb: [],
    hreflangLinks: [],
    blogCategories: [],
} satisfies TypeBlogArticleDetailFragment;

describe('getGtmPageInfoTypeForFriendlyUrl', () => {
    test('should add article UUID as articleId for article detail pages', () => {
        const result = getGtmPageInfoTypeForFriendlyUrl(articleDetail);

        expect(result).toMatchObject({
            type: GtmPageType.article_detail,
            articleId: articleDetail.uuid,
        });
    });

    test('should keep articleId for special article page types', () => {
        const result = getGtmPageInfoTypeForFriendlyUrl({
            ...articleDetail,
            slug: '/about-us',
        });

        expect(result).toMatchObject({
            type: GtmPageType.about,
            articleId: articleDetail.uuid,
        });
    });

    test('should add blog article UUID as articleId for blog article detail pages', () => {
        const result = getGtmPageInfoTypeForFriendlyUrl(blogArticleDetail);

        expect(result).toMatchObject({
            type: GtmPageType.blog_article_detail,
            articleId: blogArticleDetail.uuid,
        });
    });

    test('should return product detail page type for purchasable product detail pages', () => {
        const result = getGtmPageInfoTypeForFriendlyUrl(productDetail);

        expect(result).toMatchObject({
            type: GtmPageType.product_detail,
        });
    });

    test('should return product sold out page type for unavailable product detail pages', () => {
        const result = getGtmPageInfoTypeForFriendlyUrl({
            ...productDetail,
            isCurrentlyOutOfStock: true,
        });

        expect(result).toMatchObject({
            type: GtmPageType.product_sold_out,
        });
    });

    test('should return product sold out page type for product detail pages with out of stock availability', () => {
        const result = getGtmPageInfoTypeForFriendlyUrl({
            ...productDetail,
            availability: {
                __typename: 'Availability',
                name: 'Out of stock',
                status: TypeAvailabilityStatusEnum.OutOfStock,
            },
        });

        expect(result).toMatchObject({
            type: GtmPageType.product_sold_out,
        });
    });

    test('should return product sold out page type for selling denied product detail pages', () => {
        const result = getGtmPageInfoTypeForFriendlyUrl({
            ...productDetail,
            isSellingDenied: true,
        });

        expect(result).toMatchObject({
            type: GtmPageType.product_sold_out,
        });
    });

    test('should return product detail page type for inquiry product detail pages even when currently out of stock', () => {
        const result = getGtmPageInfoTypeForFriendlyUrl({
            ...productDetail,
            availability: {
                __typename: 'Availability',
                name: 'Out of stock',
                status: TypeAvailabilityStatusEnum.OutOfStock,
            },
            isCurrentlyOutOfStock: true,
            isInquiryType: true,
        });

        expect(result).toMatchObject({
            type: GtmPageType.product_detail,
        });
    });
});
