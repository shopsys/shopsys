import { getArticleDetailQuery } from 'app/_queries/getArticleDetailQuery';
import { getBlogArticleDetailQuery } from 'app/_queries/getBlogArticleDetailQuery';
import { getProductQuery } from 'app/_queries/getProductQuery';
import { mapToBreadcrumbFragments } from 'app/_utils/breadcrumbs';
import { DynamicBreadcrumbsSettings, StaticBreadcrumb, StaticBreadcrumbsSettings } from 'types/breadcrumbs';
import { TranslationKeys } from 'types/translation';

export const staticBreadcrumbsSettings: StaticBreadcrumbsSettings = {
    '/customer': [{ name: 'Customer' }],
    '/customer/change-password': [{ name: 'Customer', slug: '/customer/change-password' }],
    '/customer/complaints': [{ name: 'My complaints', slug: '/customer/complaints' }],
    '/customer/new-complaint': [
        { name: 'My complaints', slug: '/customer/complaints' },
        { name: 'New complaint', slug: '/customer/new-complaint' },
    ],
    '/customer/orders': [{ name: 'My orders', slug: '/customer/orders' }],
    '/customer/users': [{ name: 'Customer users', slug: '/customer/users' }],
    '/login': [{ name: 'Log in' }],
    '/new-password': [{ name: 'Set new password', slug: '/new-password' }],
    '/order-detail': [{ name: 'My orders', slug: '/customer/orders' }],
    '/personal-data-export': [{ name: 'Personal data export' }],
    '/personal-data-overview': [{ name: 'Personal data overview' }],
    '/product-comparison': [{ name: 'Product comparison', slug: '/product-comparison' }],
    '/registration': [{ name: 'Registration' }],
    '/reset-password': [{ name: 'Reset password' }],
    '/search': [{ name: 'Search', slug: '/search' }],
    '/stores': [{ name: 'Department stores' }],
    '/user-consent': [{ name: 'User consent' }],
    '/wishlist': [{ name: 'Wishlist', slug: '/wishlist' }],
};

// TODO: breadcrumbs for dynamic routes or routes with dynamic breadcrumbs (ids, numbers)
export const dynamicBreadcrumbsSettings: DynamicBreadcrumbsSettings = {
    '/products': async (pathname) => {
        const productSlug = pathname.split('/products/')[1];
        const product = await getProductQuery(productSlug);

        return product.product?.breadcrumb ?? [];
    },
    '/articles': async (pathname) => {
        const articleSlug = pathname.split('/articles/')[1];
        const articleDetail = await getArticleDetailQuery(articleSlug);
        const article = articleDetail?.__typename === 'ArticleSite' ? articleDetail : null;

        return article?.breadcrumb ?? [];
    },
    // TODO finish rest
    '/blogArticles': async (pathname) => {
        const blogArticleSlug = pathname.split('/blogArticles/')[1];
        const blogArticle = await getBlogArticleDetailQuery(blogArticleSlug);
        return blogArticle?.breadcrumb ?? [];
    },
    '/blogCategories': async () => {
        return [];
    },
    '/brands': async () => {
        return [];
    },
    '/categories': async () => {
        return [];
    },
    '/customer/complaint-detail': async (pathname, t) => {
        // TODO get correct dynamic name
        const complaintNumber = 'Complaint number' as TranslationKeys;
        const breadcrumbs: StaticBreadcrumb[] = [
            { name: 'My complaints', slug: '/customer/complaints' },
            { name: complaintNumber },
        ];

        return mapToBreadcrumbFragments(breadcrumbs, t);
    },
    '/customer/edit-profile': async (pathname, t) => {
        // TODO get correct dynamic name
        const userProfileSectionLabel = 'Edit profile' as TranslationKeys;
        const breadcrumbs: StaticBreadcrumb[] = [{ name: userProfileSectionLabel, slug: '/customer/edit-profile' }];

        return mapToBreadcrumbFragments(breadcrumbs, t);
    },
    '/customer/order-detail': async (pathname, t) => {
        // TODO get correct dynamic name
        const orderNumber = 'Order number' as TranslationKeys;
        const breadcrumbs: StaticBreadcrumb[] = [
            { name: 'My orders', slug: '/customer/orders' },
            { name: orderNumber },
        ];

        return mapToBreadcrumbFragments(breadcrumbs, t);
    },
};
