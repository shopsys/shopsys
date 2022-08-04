import { getGtmConsentInfo, getGtmPageInfoType, getGtmUserInfo, useGtmCartEventInfo } from './Gtm';
import { getGtmDeviceType } from './Helpers';
import { mapGtmCartItemType, mapGtmListedProductType, mapGtmProductDetailType, mapGtmShippingInfo } from './Mappers';
import { useCurrentUserData } from 'hooks/user/useCurrentUserData';
import { useRouter } from 'next/router';
import { useMemo } from 'react';
import { useShopsysSelector } from 'redux/main';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { CartItemType } from 'types/cart';
import {
    GtmCartInfoType,
    GtmChangeCartItemEventType,
    GtmEcommerceEventType,
    GtmEventType,
    GtmListNameType,
    GtmPageInfoType,
    GtmPageType,
    GtmPageViewEventType,
    GtmPaymentInfoEventType,
    GtmProductDetailEventType,
    GtmProductsListEventType,
    GtmSearchEventType,
    GtmSectionType,
    GtmShippingInfoEventType,
} from 'types/gtm';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { ListedProductType, MainVariantDetailType, ProductDetailType, SimpleProductType } from 'types/product';
import { AutocompleteSearchType } from 'types/search';
import { TransportType } from 'types/transport';

export const useGtmStaticPageViewEvent = (
    pageType: GtmPageType,
    breadcrumbs?: BreadcrumbItemType[],
): GtmPageViewEventType => {
    const path = useRouter().asPath;
    const gtmPageInfo = useMemo(() => getGtmPageInfoType(pageType, path, breadcrumbs), [pageType, path, breadcrumbs]);
    return useGtmPageViewEvent(gtmPageInfo);
};

export const useGtmPageViewEvent = (pageInfo: GtmPageInfoType): GtmPageViewEventType => {
    const domainConfig = useShopsysSelector((state) => state.domain);
    const { user } = useCurrentUserData();
    const cartEventInfo = useGtmCartEventInfo();

    return useMemo(
        () => ({
            event: 'page_ready',
            page: pageInfo,
            user: getGtmUserInfo(user),
            device: getGtmDeviceType(),
            consent: getGtmConsentInfo(),
            currency: domainConfig.currencyCode,
            language: domainConfig.defaultLocale,
            cart: cartEventInfo.cart,
            _clear: true,
            _isLoaded: cartEventInfo.isLoaded,
        }),
        [
            cartEventInfo.cart,
            cartEventInfo.isLoaded,
            domainConfig.currencyCode,
            domainConfig.defaultLocale,
            pageInfo,
            user,
        ],
    );
};

export const getNewGtmEcommerceEvent = (eventType: GtmEventType, clear = false): GtmEcommerceEventType => ({
    event: eventType,
    ecommerce: undefined,
    _clear: clear,
});

export const getGtmChangeCartItemEvent = (
    cartItem: CartItemType,
    listIndex: number,
    quantity: number,
    currencyCode: string,
    eventValue: number,
    eventValueWithTax: number,
    listName: GtmListNameType,
    domainUrl: string,
): GtmChangeCartItemEventType => ({
    listName,
    value: eventValue,
    valueWithTax: eventValueWithTax,
    currency: currencyCode,
    products: [mapGtmCartItemType(cartItem, listIndex, domainUrl, quantity)],
});

export const getGtmShippingInfoEvent = (
    cartInfoType: GtmCartInfoType,
    transport: TransportType,
    pickupPlace: PickupPlaceType | null,
    paymentName: string | undefined,
): GtmShippingInfoEventType => {
    const { shippingDetail, shippingExtra } = mapGtmShippingInfo(pickupPlace);

    return {
        value: cartInfoType.value,
        valueWithTax: cartInfoType.valueWithTax,
        coupons: cartInfoType.coupons,
        products: cartInfoType.products ?? [],
        currency: cartInfoType.currency,
        paymentType: paymentName,
        shippingType: transport.name,
        shippingDetail: shippingDetail,
        shippingExtra: shippingExtra,
        shippingPrice: transport.price.priceWithoutVat,
        shippingPriceWithTax: transport.price.priceWithVat,
    };
};

export const getGtmPaymentInfoEvent = (
    cartInfoType: GtmCartInfoType,
    payment: PaymentType,
): GtmPaymentInfoEventType => ({
    value: cartInfoType.value,
    valueWithTax: cartInfoType.valueWithTax,
    coupons: cartInfoType.coupons,
    products: cartInfoType.products ?? [],
    currency: cartInfoType.currency,
    paymentType: payment.name,
    paymentPrice: payment.price.priceWithoutVat,
    paymentPriceWithTax: payment.price.priceWithVat,
});

export const getGtmProductsListEvent = (
    products: ListedProductType[],
    listName: GtmListNameType,
    currentPage: number,
    pageSize: number,
    domainUrl: string,
): GtmProductsListEventType => {
    return {
        listName,
        products: products.map((product: ListedProductType, index) => {
            const listedProductIndex = (currentPage - 1) * pageSize + index;

            return mapGtmListedProductType(product, listedProductIndex, domainUrl);
        }),
    };
};

export const getGtmProductDetailOnClickEvent = (
    product: ListedProductType | SimpleProductType,
    listName: GtmListNameType,
    index: number,
    domainUrl: string,
): GtmProductsListEventType => ({
    listName,
    products: [mapGtmListedProductType(product, index, domainUrl)],
});

export const getGtmProductDetailEvent = (
    product: ProductDetailType | MainVariantDetailType,
    currencyCode: string,
    domainUrl: string,
): GtmProductDetailEventType => ({
    currency: currencyCode,
    value: product.price.priceWithoutVat,
    valueWithTax: product.price.priceWithVat,
    products: [mapGtmProductDetailType(product, domainUrl)],
});

export const getGtmSearchResultEvent = (searchResult: AutocompleteSearchType, keyword: string): GtmSearchEventType => {
    const resultsCount = searchResult.categoriesSearch.totalCount + searchResult.productsSearch.totalCount;
    const suggestResult = {
        results: resultsCount,
        keyword,
        sections: {
            categories: searchResult.categoriesSearch.totalCount,
            products: searchResult.productsSearch.totalCount,
        },
    };

    return {
        event: 'ec.suggest_result',
        suggestResult,
    };
};

export const getGtmSearchClickEvent = (
    keyword: string,
    section: GtmSectionType,
    itemName: string,
): GtmSearchEventType => ({
    event: 'ec.suggest_click',
    suggestClick: {
        keyword,
        itemName,
        section,
    },
});
