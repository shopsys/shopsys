import { getRandomPageId } from './Helpers';
import { mapGtmCartItemType, mapGtmShippingInfo } from './Mappers';
import { useCurrentCart } from 'connectors/cart/Cart';
import { MD5 } from 'crypto-js';
import { canUseDom } from 'helpers/canUseDom';
import { getUserConsentCookie } from 'helpers/cookies/getUserConsentCookie';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useMemo } from 'react';
import { useShopsysSelector } from 'redux/main';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { CartItemType, CartType } from 'types/cart';
import { CategoryDetailType } from 'types/category';
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
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';

export const useGtmCartEventInfo = (): GtmCartInfoEventType => {
    const { cart, promoCode, isInitiallyLoaded } = useCurrentCart();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { isUserLoggedIn } = useCurrentUserData();
    const { domain } = useShopsysSelector((state) => state);

    return useMemo(() => {
        if ((cartUuid === null && !isUserLoggedIn) || cart === null) {
            return { cart: null, isLoaded: isInitiallyLoaded };
        }

        const [urlCart] = getInternationalizedStaticUrls(['/cart'], domain.url);

        let products: GtmCartItemType[] | undefined = undefined;
        if (cart.items.length > 0) {
            products = cart.items.map((cartItem, index) => mapGtmCartItemType(cartItem, index));
        }

        const coupons: string[] = [];
        if (promoCode !== null) {
            coupons.push(promoCode);
        }

        return {
            cart: {
                urlCart,
                currency: domain.currencyCode,
                value: cart.totalItemsPrice.priceWithoutVat,
                valueWithTax: cart.totalItemsPrice.priceWithVat,
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
    breadcrumbs: BreadcrumbItemType[] | undefined,
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
            defaultPageInfo.type = getCategoryOrSeoCategoryGtmListName(data, slug);
            defaultPageInfo.category = getGtmCategoryInfo(data as CategoryDetailType);
            break;
        case 'Store':
            defaultPageInfo.type = 'store';
            break;
        case 'Article':
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
    breadcrumbs: BreadcrumbItemType[] | undefined,
): GtmPageInfoType => ({
    type: pageType,
    path,
    pageId: getRandomPageId(),
    breadcrumbs: breadcrumbs ?? [],
});

const getGtmCategoryInfo = (category: CategoryDetailType) => {
    return [category.name];
};

export const gtmSafePushEvent = (event: GtmPageViewEventType | GtmEcommerceEventType | GtmSearchEventType): void => {
    if (canUseDom()) {
        window.dataLayer = window.dataLayer ?? [];
        window.dataLayer.push(event);
    }
};

export const getGtmPurchaseData = (
    cart: CartType,
    transport: TransportType,
    pickupPlace: PickupPlaceType | null,
    payment: PaymentType,
    promoCode: string | null,
    orderNumber: string,
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
        discountAmount: cart.totalDiscountPrice.priceWithVat,
        value: cart.totalPrice.priceWithoutVat,
        valueWithTax: cart.totalPrice.priceWithVat,
        valueTax: cart.totalPrice.vatAmount,
        currency: cart.totalPrice.currencyCode,
        products: cart.items.map((cartItem: CartItemType, index) => mapGtmCartItemType(cartItem, index)),
        paymentType: payment.name,
        paymentPrice: payment.price.priceWithoutVat,
        paymentPriceWithTax: payment.price.priceWithVat,
        shippingType: transport.name,
        shippingDetail: shippingDetail,
        shippingExtra: shippingExtra,
        shippingPrice: transport.price.priceWithoutVat,
        shippingPriceWithTax: transport.price.priceWithVat,
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

export const getCategoryOrSeoCategoryGtmListName = (
    data: CategoryDetailType,
    slug: string,
): 'seo category' | 'category' =>
    data.readyCategorySeoMixLinks.some((seoMixLink) => {
        const slugWithoutLeadingSlash = slug.charAt(0) === '/' ? slug.slice(1) : slug;
        return seoMixLink.slug === slugWithoutLeadingSlash;
    })
        ? 'seo category'
        : 'category';
