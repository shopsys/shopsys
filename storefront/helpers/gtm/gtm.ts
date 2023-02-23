import { getRandomPageId } from './helpers';
import { mapGtmCartItemType, mapGtmShippingInfo } from './mappers';
import { useCurrentCart } from 'connectors/cart/Cart';
import { MD5 } from 'crypto-js';
import {
    BreadcrumbFragmentApi,
    ListedStoreFragmentApi,
    SimplePaymentFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import { getUserConsentCookie } from 'helpers/cookies/getUserConsentCookie';
import { DomainConfigType } from 'helpers/domain/domain';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { canUseDom } from 'helpers/misc/canUseDom';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useMemo } from 'react';
import { useShopsysSelector } from 'redux/main';
import { CartType } from 'types/cart';
import { CurrentCustomerType } from 'types/customer';
import { FriendlyUrlPageType } from 'types/friendlyUrl';
import {
    GtmCartInfoEventType,
    GtmCartItemType,
    GtmConsentInfoType,
    GtmEcommerceEventType,
    GtmPageInfoType,
    GtmPageType,
    GtmPageViewEventType,
    GtmPurchaseType,
    GtmReviewConsentsType,
    GtmSearchEventType,
    GtmUserInfoType,
} from 'types/gtm';

export const useGtmCartEventInfo = (): GtmCartInfoEventType => {
    const { cart, promoCode, isInitiallyLoaded } = useCurrentCart();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { isUserLoggedIn } = useCurrentUserData();
    const { domain } = useShopsysSelector((state) => state);

    return useMemo(() => {
        if ((cartUuid === null && !isUserLoggedIn) || cart === null) {
            return { cart: null, isLoaded: isInitiallyLoaded };
        }

        let products: GtmCartItemType[] | undefined = undefined;
        if (cart.items.length > 0) {
            products = cart.items.map((cartItem, index) => mapGtmCartItemType(cartItem, domain.url, index));
        }

        const coupons: string[] = [];
        if (promoCode !== null) {
            coupons.push(promoCode);
        }

        let urlCart;
        if (isUserLoggedIn) {
            const [loginRelativeUrl, cartRelativeUrl] = getInternationalizedStaticUrls(['/login', '/cart'], domain.url);
            const loginAbsoluteUrlWithoutLeadingSlash = loginRelativeUrl.slice(1);
            urlCart = domain.url + loginAbsoluteUrlWithoutLeadingSlash + '?r=' + cartRelativeUrl;
        } else {
            const [abandonedCartRelativeUrl] = getInternationalizedStaticUrls(
                [{ url: '/abandoned-cart/:cartUuid', param: cartUuid }],
                domain.url,
            );
            const abandonedCartRelativeUrlWithoutLeadingSlash = abandonedCartRelativeUrl.slice(1);
            urlCart = domain.url + abandonedCartRelativeUrlWithoutLeadingSlash;
        }

        return {
            cart: {
                urlCart,
                currency: domain.currencyCode,
                value: Number.parseFloat(cart.totalItemsPrice.priceWithoutVat),
                valueWithTax: Number.parseFloat(cart.totalItemsPrice.priceWithVat),
                products,
                coupons,
            },
            isLoaded: isInitiallyLoaded,
        };
    }, [cart, cartUuid, domain.currencyCode, domain.url, isInitiallyLoaded, isUserLoggedIn, promoCode]);
};

export const getGtmPageInfoForFriendlyUrl = (
    data: FriendlyUrlPageType | null | undefined,
    slug: string,
    breadcrumbs: BreadcrumbFragmentApi[] | undefined,
): GtmPageInfoType => {
    const defaultPageInfo: GtmPageInfoType = {
        type: '404',
        path: slug,
        pageId: getRandomPageId(),
        breadcrumbs: breadcrumbs ?? [],
    };

    if (data === null || data === undefined) {
        return defaultPageInfo;
    }

    switch (data.__typename) {
        case 'RegularProduct':
        case 'Variant':
        case 'MainVariant':
            defaultPageInfo.type = 'product';
            break;
        case 'Category':
            defaultPageInfo.type = getCategoryOrSeoCategoryGtmListName(data.originalCategorySlug);
            defaultPageInfo.category = [data.name];
            break;
        case 'Store':
            defaultPageInfo.type = 'store';
            break;
        case 'ArticleSite':
            defaultPageInfo.type = 'text';
            break;
        case 'BlogArticle':
            defaultPageInfo.type = 'article';
            defaultPageInfo.articleId = data.uuid;
            break;
        case 'Brand':
            defaultPageInfo.type = 'brand';
            break;
        case 'Flag':
            defaultPageInfo.type = 'flag';
            break;
        case 'BlogCategory':
            defaultPageInfo.type = 'blog';
            break;
        default:
            break;
    }

    return defaultPageInfo;
};

export const getGtmPageInfoType = (
    pageType: GtmPageType,
    path: string,
    breadcrumbs: BreadcrumbFragmentApi[] | undefined,
): GtmPageInfoType => ({
    type: pageType,
    path,
    pageId: getRandomPageId(),
    breadcrumbs: breadcrumbs ?? [],
});

export const gtmSafePushEvent = (event: GtmPageViewEventType | GtmEcommerceEventType | GtmSearchEventType): void => {
    if (canUseDom()) {
        window.dataLayer = window.dataLayer ?? [];
        window.dataLayer.push(event);
    }
};

export const getGtmPurchaseData = (
    cart: CartType,
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi,
    pickupPlace: ListedStoreFragmentApi | null,
    payment: SimplePaymentFragmentApi,
    promoCode: string | null,
    orderNumber: string,
    domainConfig: DomainConfigType,
): GtmPurchaseType => {
    const coupons: string[] = [];
    if (promoCode !== null) {
        coupons.push(promoCode);
    }

    const { shippingDetail, shippingExtra } = mapGtmShippingInfo(pickupPlace);

    return {
        reviewConsents: getGtmReviewConsents(),
        id: orderNumber,
        coupons: coupons,
        discountAmount: Number.parseFloat(cart.totalDiscountPrice.priceWithVat),
        value: Number.parseFloat(cart.totalPrice.priceWithoutVat),
        valueWithTax: Number.parseFloat(cart.totalPrice.priceWithVat),
        valueTax: Number.parseFloat(cart.totalPrice.vatAmount),
        currency: domainConfig.currencyCode,
        products: cart.items.map((cartItem, index) => mapGtmCartItemType(cartItem, domainConfig.url, index)),
        paymentType: payment.name,
        paymentPrice: payment.price.priceWithoutVat,
        paymentPriceWithTax: payment.price.priceWithVat,
        shippingType: transport.name,
        shippingDetail: shippingDetail,
        shippingExtra: shippingExtra,
        shippingPrice: Number.parseFloat(transport.price.priceWithoutVat),
        shippingPriceWithTax: Number.parseFloat(transport.price.priceWithVat),
    };
};

const getGtmReviewConsents = (): GtmReviewConsentsType => ({
    google: true,
    seznam: true,
    heureka: true,
});

export const getGtmConsentInfo = (): GtmConsentInfoType => {
    const userConsentCookie = getUserConsentCookie();

    return {
        marketing: userConsentCookie?.marketing ? 'granted' : 'denied',
        statistics: userConsentCookie?.statistics ? 'granted' : 'denied',
        preferences: userConsentCookie?.preferences ? 'granted' : 'denied',
    };
};

export const getGtmUserInfo = (currentCustomer: CurrentCustomerType | null | undefined): GtmUserInfoType => {
    const userInfo: GtmUserInfoType = {
        type: 'visitor',
    };

    if (currentCustomer !== undefined && currentCustomer !== null) {
        userInfo.type = 'customer';
        userInfo.id = currentCustomer.uuid;
        userInfo.email = currentCustomer.email;
        userInfo.emailHash = MD5(currentCustomer.email).toString();
        userInfo.phoneNumber = currentCustomer.telephone;
        userInfo.name = currentCustomer.firstName;
        userInfo.surname = currentCustomer.lastName;
        userInfo.street = currentCustomer.street;
        userInfo.city = currentCustomer.city;
        userInfo.psc = currentCustomer.postcode;
        userInfo.country = currentCustomer.country.code;
        userInfo.group = currentCustomer.pricingGroup;
    }

    return userInfo;
};

export const getCategoryOrSeoCategoryGtmListName = (originalCategorySlug: string | null): 'seo category' | 'category' =>
    originalCategorySlug !== null ? 'seo category' : 'category';
