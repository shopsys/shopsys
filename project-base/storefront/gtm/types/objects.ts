import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { TypeLoginTypeEnum } from 'graphql/types';
import { GtmConsent } from 'gtm/enums/GtmConsent';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { GtmUserStatus } from 'gtm/enums/GtmUserStatus';
import { GtmUserType } from 'gtm/enums/GtmUserType';

export const SPECIAL_ARTICLE_GTM_TYPES = Object.freeze({
    '/about-us': GtmPageType.about,
    '/o-nas': GtmPageType.about,
} as const);

export type SpecialArticleGtmPageType = (typeof SPECIAL_ARTICLE_GTM_TYPES)[keyof typeof SPECIAL_ARTICLE_GTM_TYPES];

export type GtmReviewConsentsType = {
    seznam: boolean;
    google: boolean;
    heureka: boolean;
};

export type GtmPageInfoInterface<PageType = GtmPageType, ExtendedPageProperties = object> = ExtendedPageProperties & {
    type: PageType;
    pageId: string;
    breadcrumbs: TypeBreadcrumbFragment[];
};

export type GtmCategoryDetailPageInfoType = GtmPageInfoInterface<
    GtmPageType.category_detail | GtmPageType.seo_category_detail,
    {
        category: string[];
        categoryId: number[];
    }
>;

export type GtmBlogArticleDetailPageInfoType = GtmPageInfoInterface<
    GtmPageType.blog_article_detail,
    {
        articleId: string;
    }
>;

export type GtmProductDetailPageInfoType = GtmPageInfoInterface<
    GtmPageType.product_detail | GtmPageType.product_sold_out
>;

export type GtmArticleDetailPageInfoType = GtmPageInfoInterface<
    GtmPageType.article_detail | SpecialArticleGtmPageType,
    {
        articleId: string;
    }
>;

export type GtmBrandDetailPageInfoType = GtmPageInfoInterface<
    GtmPageType.brand_detail,
    {
        brandId: number;
    }
>;

export type GtmPageInfoType =
    | GtmCategoryDetailPageInfoType
    | GtmProductDetailPageInfoType
    | GtmBlogArticleDetailPageInfoType
    | GtmArticleDetailPageInfoType
    | GtmBrandDetailPageInfoType
    | GtmPageInfoInterface;

export type GtmCartInfoType = {
    abandonedCartUrl: string | undefined;
    currencyCode: string;
    valueWithoutVat: number | null;
    valueWithVat: number | null;
    products: GtmCartItemType[] | undefined;
    promoCodes?: string[];
};

export type GtmUserInfoType = {
    id?: string;
    email?: string;
    emailHash?: string;
    firstName?: string;
    lastName?: string;
    telephone?: string;
    street?: string;
    city?: string;
    postcode?: string;
    country?: string;
    type?: GtmUserType;
    status: GtmUserStatus;
    group?: string;
    loginType?: TypeLoginTypeEnum;
    externalId?: string | null;
    newsletterSubscription?: boolean;
};

export type GtmConsentInfoType = {
    statistics: GtmConsent;
    marketing: GtmConsent;
    preferences: GtmConsent;
};

export type GtmProductInterface = {
    id: number;
    name: string;
    availability: string;
    flags: string[];
    priceWithoutVat: number | null;
    priceWithVat: number | null;
    vatAmount: number;
    sku: string;
    url: string;
    brand: string;
    categories: string[];
    imageUrl?: string;
};

export type GtmListedProductType = GtmProductInterface & {
    listIndex?: number;
};

export type GtmCartItemType = GtmListedProductType & {
    quantity: number;
    variant?: string;
};

export type GtmShippingInfoType = {
    transportDetail: string;
    transportExtra: string[];
};

export type GtmUserEntryInfoType = {
    id?: string;
    email?: string;
    firstName?: string;
    lastName?: string;
    loginType?: TypeLoginTypeEnum;
    externalId?: string | null;
};
