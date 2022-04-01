import { mapGtmCartItemType, mapGtmShippingInfo } from './Mappers';
import { useCurrentCart } from 'connectors/cart/Cart';
import { canUseDom } from 'helpers/canUseDom';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useShopsysSelector } from 'redux/main';
import { BlogArticleDetailType } from 'types/blogArticle';
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
    const { cart, promoCode, isLoaded } = useCurrentCart();
    const { cartUuid } = useShopsysSelector((state) => state.user);
    const { isUserLoggedIn } = useCurrentUserData();
    const { domain } = useShopsysSelector((state) => state);

    if ((cartUuid === null && !isUserLoggedIn) || cart === null) {
        return { cart: null, isLoaded };
    }

    const [urlCart] = getInternationalizedStaticUrls(['/cart'], domain.url);

    let products: GtmCartItemType[] | undefined = undefined;
    if (cart.items.length > 0) {
        products = cart.items.map((cartItem) => mapGtmCartItemType(cartItem));
    }

    const coupons: string[] = [];
    if (promoCode !== null) {
        coupons.push(promoCode);
    }

    return {
        cart: {
            urlCart,
            currency: domain.currencyCode,
            value: cart.totalPrice.priceWithoutVat,
            valueWithTax: cart.totalPrice.priceWithVat,
            products,
            coupons,
        },
        isLoaded,
    };
};

export const getGtmPageInfoForFriendlyUrl = (
    data: FriendlyUrlPageType | null | undefined,
    slug: string,
): GtmPageInfoType => {
    const defaultPageInfo: GtmPageInfoType = {
        type: '404',
        path: slug,
    };

    if (data === null || data === undefined) {
        return defaultPageInfo;
    }

    if (data.__typename === 'RegularProduct' || data.__typename === 'Variant' || data.__typename === 'MainVariant') {
        defaultPageInfo.type = 'product';
    } else if (data.__typename === 'Category') {
        defaultPageInfo.type = 'category';
        defaultPageInfo.category = getGtmCategoryInfo(data as CategoryDetailType);
    } else if (data.__typename === 'Store') {
        defaultPageInfo.type = 'store';
    } else if (data.__typename === 'Article') {
        defaultPageInfo.type = 'text';
    } else if (data.__typename === 'BlogArticle') {
        const blogArticle = data as BlogArticleDetailType;
        defaultPageInfo.type = 'article';
        defaultPageInfo.id = blogArticle.uuid;
    } else if (data.__typename === 'Brand') {
        defaultPageInfo.type = 'brand';
    } else if (data.__typename === 'Flag') {
        defaultPageInfo.type = 'flag';
    } else if (data.__typename === 'BlogCategory') {
        defaultPageInfo.type = 'blog';
    }

    return defaultPageInfo;
};

export const getGtmPageInfoType = (pageType: GtmPageType, path: string): GtmPageInfoType => ({
    type: pageType,
    path,
});

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
        revenue: cart.totalPrice.priceWithoutVat,
        revenueWithTax: cart.totalPrice.priceWithVat,
        revenueTax: cart.totalPrice.vatAmount,
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

export const getGtmConsentInfo = (): GtmConsentInfoType => ({
    functional: 'granted',
    marketing: 'granted',
    targeting: 'granted',
    statistics: 'granted',
    performance: 'granted',
    preferences: 'granted',
});

export const getGtmUserInfo = (
    currentCustomer: CurrentCustomerType | undefined,
    isUserLoggedIn: boolean,
): GtmUserInfoType => {
    const userInfo: GtmUserInfoType = {
        type: 'visitor',
        group: 'b2c',
    };

    if (isUserLoggedIn && currentCustomer !== undefined) {
        userInfo.type = 'customer';
        userInfo.id = currentCustomer.uuid;
        userInfo.email = currentCustomer.email;
        userInfo.phoneNumber = currentCustomer.telephone;
        userInfo.name = currentCustomer.firstName;
        userInfo.surname = currentCustomer.lastName;
        userInfo.street = currentCustomer.street;
        userInfo.city = currentCustomer.city;
        userInfo.psc = currentCustomer.postcode;
        userInfo.country = currentCustomer.country.code;
        userInfo.group = userGroup;
    }

    return userInfo;
};
