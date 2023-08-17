import { BreadcrumbFragmentApi } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmConsent, GtmPageType, GtmUserStatus, GtmUserType } from './enums';
export type GtmReviewConsentsType = {
    seznam: boolean;
    google: boolean;
    heureka: boolean;
};

export type GtmPageInfoInterface<PageType = GtmPageType, ExtendedPageProperties = object> = ExtendedPageProperties & {
    type: PageType;
    pageId: string;
    breadcrumbs: BreadcrumbFragmentApi[];
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
        articleId: number;
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
    | GtmBlogArticleDetailPageInfoType
    | GtmBrandDetailPageInfoType
    | GtmPageInfoInterface;

export type GtmCartInfoType = {
    abandonedCartUrl: string | undefined;
    currencyCode: string;
    valueWithoutVat: number;
    valueWithVat: number;
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
    priceWithoutVat: number;
    priceWithVat: number;
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
};

export type GtmShippingInfoType = {
    transportDetail: string;
    transportExtra: string[];
};
