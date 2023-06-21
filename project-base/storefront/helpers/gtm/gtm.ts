import { getRandomPageId } from './helpers';
import { mapGtmCartItemType } from './mappers';
import { useCurrentCart } from 'connectors/cart/Cart';
import { SHA256 } from 'crypto-js';
import {
    BlogArticleDetailFragmentApi,
    BrandDetailFragmentApi,
    BreadcrumbFragmentApi,
    CartFragmentApi,
    CategoryDetailFragmentApi,
} from 'graphql/generated';
import { getUserConsentCookie } from 'helpers/cookies/getUserConsentCookie';
import { DomainConfigType } from 'helpers/domain/domain';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { canUseDom } from 'helpers/misc/canUseDom';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useMemo } from 'react';
import { ContactInformation } from 'store/zustand/slices/createContactInformationSlice';
import { usePersistStore } from 'store/zustand/usePersistStore';
import { CurrentCustomerType, CustomerTypeEnum } from 'types/customer';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import {
    GtmConsent,
    GtmEventType,
    GtmPageType,
    GtmProductListNameType,
    GtmUserStatus,
    GtmUserType,
} from 'types/gtm/enums';
import { GtmEventInterface } from 'types/gtm/events';
import {
    GtmBlogArticleDetailPageInfoType,
    GtmBrandDetailPageInfoType,
    GtmCartInfoType,
    GtmCategoryDetailPageInfoType,
    GtmConsentInfoType,
    GtmPageInfoInterface,
    GtmPageInfoType,
    GtmReviewConsentsType,
    GtmUserInfoType,
} from 'types/gtm/objects';

export const useGtmCartInfo = (): { gtmCartInfo: GtmCartInfoType | null; isCartLoaded: boolean } => {
    const { cart, isInitiallyLoaded, promoCode } = useCurrentCart();
    const cartUuid = usePersistStore((s) => s.cartUuid);
    const { isUserLoggedIn } = useCurrentUserData();
    const domainConfig = useDomainConfig();

    return useMemo(() => {
        if ((cartUuid === null && !isUserLoggedIn) || cart === null) {
            return { gtmCartInfo: null, isCartLoaded: isInitiallyLoaded };
        }

        return {
            gtmCartInfo: getGtmMappedCart(cart, promoCode, isUserLoggedIn, domainConfig, cartUuid),
            isCartLoaded: isInitiallyLoaded,
        };
    }, [cart, cartUuid, domainConfig, isInitiallyLoaded, isUserLoggedIn, promoCode]);
};

export const getGtmMappedCart = (
    cart: CartFragmentApi,
    promoCode: string | null,
    isUserLoggedIn: boolean,
    domain: DomainConfigType,
    cartUuid: string | null,
): GtmCartInfoType => {
    const products = (() => {
        if (cart.items.length) {
            return cart.items.map((cartItem, index) => mapGtmCartItemType(cartItem, domain.url, index));
        }

        return undefined;
    })();

    const abandonedCartUrl = getAbandonedCartUrl(isUserLoggedIn, domain, cartUuid);

    const mappedCart: GtmCartInfoType = {
        abandonedCartUrl,
        currencyCode: domain.currencyCode,
        valueWithoutVat: parseFloat(cart.totalItemsPrice.priceWithoutVat),
        valueWithVat: parseFloat(cart.totalItemsPrice.priceWithVat),
        products,
    };

    if (promoCode !== null) {
        mappedCart.promoCodes = [promoCode];
    }

    return mappedCart;
};

const getAbandonedCartUrl = (isUserLoggedIn: boolean, domain: DomainConfigType, cartUuid: string | null) => {
    if (isUserLoggedIn) {
        const [loginRelativeUrl, cartRelativeUrl] = getInternationalizedStaticUrls(['/login', '/cart'], domain.url);
        const loginAbsoluteUrlWithoutLeadingSlash = loginRelativeUrl.slice(1);

        return domain.url + loginAbsoluteUrlWithoutLeadingSlash + '?r=' + cartRelativeUrl;
    }

    const [abandonedCartRelativeUrl] = getInternationalizedStaticUrls(
        [{ url: '/abandoned-cart/:cartUuid', param: cartUuid }],
        domain.url,
    );
    const abandonedCartRelativeUrlWithoutLeadingSlash = abandonedCartRelativeUrl.slice(1);

    return domain.url + abandonedCartRelativeUrlWithoutLeadingSlash;
};

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
    categoryDetailData: CategoryDetailFragmentApi,
): GtmCategoryDetailPageInfoType => ({
    ...defaultPageInfo,
    type: getCategoryOrSeoCategoryGtmPageType(categoryDetailData.originalCategorySlug),
    category: categoryDetailData.breadcrumb.map((item: BreadcrumbFragmentApi) => {
        return item.name;
    }),
    categoryId: [categoryDetailData.id],
});

const getPageInfoForBlogArticleDetailPage = (
    defaultPageInfo: GtmPageInfoType,
    blogArticleDetailData: BlogArticleDetailFragmentApi,
): GtmBlogArticleDetailPageInfoType => ({
    ...defaultPageInfo,
    type: GtmPageType.blog_article_detail,
    articleId: blogArticleDetailData.id,
});

const getPageInfoForBrandDetailPage = (
    defaultPageInfo: GtmPageInfoType,
    brandDetailData: BrandDetailFragmentApi,
): GtmBrandDetailPageInfoType => ({
    ...defaultPageInfo,
    type: GtmPageType.brand_detail,
    brandId: brandDetailData.id,
});

export const getGtmPageInfoType = (
    pageType: GtmPageType,
    breadcrumbs?: BreadcrumbFragmentApi[],
): GtmPageInfoInterface => ({
    type: pageType,
    pageId: getRandomPageId(),
    breadcrumbs: breadcrumbs ?? [],
});

export const getGtmReviewConsents = (): GtmReviewConsentsType => ({
    google: true,
    seznam: true,
    heureka: true,
});

export const gtmSafePushEvent = (event: GtmEventInterface<GtmEventType, unknown>): void => {
    if (canUseDom()) {
        window.dataLayer = window.dataLayer ?? [];
        window.dataLayer.push(event);
    }
};

export const getGtmConsentInfo = (): GtmConsentInfoType => {
    const userConsentCookie = getUserConsentCookie();

    return {
        marketing: userConsentCookie?.marketing ? GtmConsent.granted : GtmConsent.denied,
        statistics: userConsentCookie?.statistics ? GtmConsent.granted : GtmConsent.denied,
        preferences: userConsentCookie?.preferences ? GtmConsent.granted : GtmConsent.denied,
    };
};

export const getGtmUserInfo = (
    currentSignedInCustomer: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
): GtmUserInfoType => {
    const userInfo: GtmUserInfoType = getGtmUserInfoForVisitor(userContactInformation);

    if (currentSignedInCustomer) {
        overwriteGtmUserInfoWithLoggedCustomer(userInfo, currentSignedInCustomer, userContactInformation);
    }

    return userInfo;
};

const getGtmUserInfoForVisitor = (userContactInformation: ContactInformation) => ({
    status: GtmUserStatus.visitor,
    ...(userContactInformation.city.length > 0 && { city: userContactInformation.city }),
    ...(userContactInformation.country.value.length > 0 && { country: userContactInformation.country.value }),
    ...(userContactInformation.email.length > 0 && { email: userContactInformation.email }),
    ...(userContactInformation.email.length > 0 && { emailHash: SHA256(userContactInformation.email).toString() }),
    ...(userContactInformation.firstName.length > 0 && { firstName: userContactInformation.firstName }),
    ...(userContactInformation.telephone.length > 0 && { telephone: userContactInformation.telephone }),
    ...(userContactInformation.postcode.length > 0 && { postcode: userContactInformation.postcode }),
    ...(userContactInformation.street.length > 0 && { street: userContactInformation.street }),
    ...(userContactInformation.lastName.length > 0 && { lastName: userContactInformation.lastName }),
    type: getGtmUserType(userContactInformation.customer),
});

const getGtmUserType = (customerType: CustomerTypeEnum | undefined): GtmUserType | undefined => {
    if (customerType === undefined) {
        return undefined;
    }

    if (customerType === CustomerTypeEnum.CompanyCustomer) {
        return GtmUserType.b2b;
    }

    return GtmUserType.b2c;
};

const overwriteGtmUserInfoWithLoggedCustomer = (
    userInfo: GtmUserInfoType,
    currentSignedInCustomer: CurrentCustomerType,
    userContactInformation: ContactInformation,
) => {
    userInfo.status = GtmUserStatus.customer;
    userInfo.id = currentSignedInCustomer.uuid;
    userInfo.group = currentSignedInCustomer.pricingGroup;

    if (userInfo.street === undefined || userInfo.street.length === 0) {
        userInfo.street = currentSignedInCustomer.street;
    }
    if (userInfo.city === undefined || userInfo.city.length === 0) {
        userInfo.city = currentSignedInCustomer.city;
    }
    if (userInfo.postcode === undefined || userInfo.postcode.length === 0) {
        userInfo.postcode = currentSignedInCustomer.postcode;
    }
    if (userInfo.country === undefined || userInfo.country.length === 0) {
        userInfo.country = currentSignedInCustomer.country.code;
    }
    if (userInfo.email === undefined || userInfo.email.length === 0) {
        userInfo.email = userContactInformation.email || currentSignedInCustomer.email;
    }
    if (userInfo.emailHash === undefined || userInfo.emailHash.length === 0) {
        userInfo.emailHash = SHA256(currentSignedInCustomer.email).toString();
    }
    if (userInfo.telephone === undefined || userInfo.telephone.length === 0) {
        userInfo.telephone = currentSignedInCustomer.telephone;
    }
    if (userInfo.firstName === undefined || userInfo.firstName.length === 0) {
        userInfo.firstName = currentSignedInCustomer.firstName;
    }
    if (userInfo.lastName === undefined || userInfo.lastName.length === 0) {
        userInfo.lastName = currentSignedInCustomer.lastName;
    }

    if (userInfo.type !== undefined) {
        return;
    }

    if (currentSignedInCustomer.companyCustomer) {
        userInfo.type = GtmUserType.b2b;
    } else {
        userInfo.type = GtmUserType.b2c;
    }
};

export const getCategoryOrSeoCategoryGtmProductListName = (
    originalCategorySlug: string | null,
): GtmProductListNameType.seo_category_detail | GtmProductListNameType.category_detail =>
    originalCategorySlug ? GtmProductListNameType.seo_category_detail : GtmProductListNameType.category_detail;

export const getCategoryOrSeoCategoryGtmPageType = (
    originalCategorySlug: string | null,
): GtmPageType.seo_category_detail | GtmPageType.category_detail =>
    originalCategorySlug ? GtmPageType.seo_category_detail : GtmPageType.category_detail;
