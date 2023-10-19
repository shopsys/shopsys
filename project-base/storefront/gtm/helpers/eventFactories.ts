import {
    getGtmConsentInfo,
    getGtmPageInfoType,
    getGtmPageInfoTypeForFriendlyUrl,
    getGtmUserInfo,
    useGtmCartInfo,
} from './gtm';
import { getGtmDeviceType } from './helpers';
import { mapGtmCartItemType, mapGtmListedProductType, mapGtmProductDetailType, mapGtmShippingInfo } from './mappers';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import {
    AutocompleteSearchQueryApi,
    BreadcrumbFragmentApi,
    CartFragmentApi,
    CartItemFragmentApi,
    ListedProductFragmentApi,
    ListedStoreFragmentApi,
    MainVariantDetailFragmentApi,
    ProductDetailFragmentApi,
    SimplePaymentFragmentApi,
    SimpleProductFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import {
    GtmEventType,
    GtmFormType,
    GtmMessageDetailType,
    GtmMessageOriginType,
    GtmMessageType,
    GtmPageType,
    GtmProductListNameType,
    GtmSectionType,
} from 'gtm/types/enums';
import {
    GtmAutocompleteResultClickEventType,
    GtmAutocompleteResultsViewEventType,
    GtmCartViewEventType,
    GtmChangeCartItemEventType,
    GtmConsentUpdateEventType,
    GtmContactInformationPageViewEventType,
    GtmCreateOrderEventOrderPartType,
    GtmCreateOrderEventType,
    GtmPageViewEventType,
    GtmPaymentAndTransportPageViewEventType,
    GtmPaymentChangeEventType,
    GtmPaymentFailEventType,
    GtmProductClickEventType,
    GtmProductDetailViewEventType,
    GtmProductListViewEventType,
    GtmSendFormEventType,
    GtmShowMessageEventType,
    GtmTransportChangeEventType,
} from 'gtm/types/events';
import {
    GtmCartInfoType,
    GtmCartItemType,
    GtmConsentInfoType,
    GtmPageInfoType,
    GtmReviewConsentsType,
    GtmUserInfoType,
} from 'gtm/types/objects';
import { DomainConfigType } from 'helpers/domain/domainConfig';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import { useMemo } from 'react';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { CurrentCustomerType } from 'types/customer';
import { UserConsentFormType } from 'types/form';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export const getGtmCartViewEvent = (
    currencyCode: string,
    valueWithoutVat: number,
    valueWithVat: number,
    products: GtmCartItemType[] | undefined,
): GtmCartViewEventType => ({
    event: GtmEventType.cart_view,
    ecommerce: {
        currencyCode,
        valueWithoutVat,
        valueWithVat,
        products,
    },
    _clear: true,
});

export const getGtmContactInformationPageViewEvent = (
    gtmCartInfo: GtmCartInfoType,
): GtmContactInformationPageViewEventType => ({
    event: GtmEventType.contact_information_page_view,
    ecommerce: {
        currencyCode: gtmCartInfo.currencyCode,
        valueWithoutVat: gtmCartInfo.valueWithoutVat,
        valueWithVat: gtmCartInfo.valueWithVat,
        promoCodes: gtmCartInfo.promoCodes,
        products: gtmCartInfo.products,
    },
    _clear: true,
});

export const getGtmPaymentAndTransportPageViewEvent = (
    currencyCode: string,
    gtmCartInfo: GtmCartInfoType,
): GtmPaymentAndTransportPageViewEventType => ({
    event: GtmEventType.payment_and_transport_page_view,
    ecommerce: {
        currencyCode,
        valueWithoutVat: gtmCartInfo.valueWithoutVat,
        valueWithVat: gtmCartInfo.valueWithVat,
        products: gtmCartInfo.products,
    },
    _clear: true,
});

export const getGtmPaymentFailEvent = (orderId: string): GtmPaymentFailEventType => ({
    event: GtmEventType.payment_fail,
    paymentFail: {
        id: orderId,
    },
    _clear: true,
});

export const getGtmCreateOrderEvent = (
    gtmCreateOrderEventOrderPart: GtmCreateOrderEventOrderPartType,
    gtmCreateOrderEventUserPart: GtmUserInfoType,
    isPaymentSuccessful?: boolean,
): GtmCreateOrderEventType => ({
    event: GtmEventType.create_order,
    ecommerce: {
        ...gtmCreateOrderEventOrderPart,
        isPaymentSuccessful,
    },
    user: gtmCreateOrderEventUserPart,
    _clear: true,
});

export const getGtmCreateOrderEventOrderPart = (
    cart: CartFragmentApi,
    payment: SimplePaymentFragmentApi,
    promoCode: string | null,
    orderNumber: string,
    reviewConsents: GtmReviewConsentsType,
    domainConfig: DomainConfigType,
): GtmCreateOrderEventOrderPartType => ({
    currencyCode: domainConfig.currencyCode,
    id: orderNumber,
    valueWithoutVat: parseFloat(cart.totalPrice.priceWithoutVat),
    valueWithVat: parseFloat(cart.totalPrice.priceWithVat),
    vatAmount: parseFloat(cart.totalPrice.vatAmount),
    paymentPriceWithoutVat: mapPriceForCalculations(payment.price.priceWithoutVat),
    paymentPriceWithVat: mapPriceForCalculations(payment.price.priceWithVat),
    promoCodes: promoCode !== null ? [promoCode] : undefined,
    paymentType: payment.name,
    reviewConsents,
    products: cart.items.map((cartItem, index) => mapGtmCartItemType(cartItem, domainConfig.url, index)),
});

export const getGtmCreateOrderEventUserPart = (
    user: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
): GtmUserInfoType => getGtmUserInfo(user, userContactInformation);

export const getGtmSendFormEvent = (form: GtmFormType): GtmSendFormEventType => ({
    event: GtmEventType.send_form,
    eventParameters: {
        form,
    },
    _clear: true,
});

export const getGtmProductClickEvent = (
    product: ListedProductFragmentApi | SimpleProductFragmentApi,
    gtmProductListName: GtmProductListNameType,
    listIndex: number,
    domainUrl: string,
): GtmProductClickEventType => ({
    event: GtmEventType.product_click,
    ecommerce: {
        listName: gtmProductListName,
        products: [mapGtmListedProductType(product, listIndex, domainUrl)],
    },
    _clear: true,
});

export const getGtmProductDetailViewEvent = (
    product: ProductDetailFragmentApi | MainVariantDetailFragmentApi,
    currencyCodeCode: string,
    domainUrl: string,
): GtmProductDetailViewEventType => ({
    event: GtmEventType.product_detail_view,
    ecommerce: {
        currencyCode: currencyCodeCode,
        valueWithoutVat: parseFloat(product.price.priceWithoutVat),
        valueWithVat: parseFloat(product.price.priceWithVat),
        products: [mapGtmProductDetailType(product, domainUrl)],
    },
    _clear: true,
});

export const getGtmAutocompleteResultsViewEvent = (
    searchResult: AutocompleteSearchQueryApi,
    keyword: string,
): GtmAutocompleteResultsViewEventType => {
    const resultsCount =
        searchResult.categoriesSearch.totalCount +
        searchResult.productsSearch.totalCount +
        searchResult.brandSearch.length +
        searchResult.articlesSearch.length;
    const suggestResult: GtmAutocompleteResultsViewEventType['autocompleteResults'] = {
        keyword,
        results: resultsCount,
        sections: {
            category: searchResult.categoriesSearch.totalCount,
            product: searchResult.productsSearch.totalCount,
            brand: searchResult.brandSearch.length,
            article: searchResult.articlesSearch.length,
        },
    };

    return {
        event: GtmEventType.autocomplete_results_view,
        autocompleteResults: suggestResult,
        _clear: true,
    };
};

export const getGtmAutocompleteResultClickEvent = (
    keyword: string,
    section: GtmSectionType,
    itemName: string,
): GtmAutocompleteResultClickEventType => ({
    event: GtmEventType.autocomplete_result_click,
    autocompleteResultClick: {
        keyword,
        itemName,
        section,
    },
    _clear: true,
});

export const useGtmStaticPageViewEvent = (
    pageType: GtmPageType,
    breadcrumbs?: BreadcrumbFragmentApi[],
): GtmPageViewEventType => {
    const { gtmCartInfo, isCartLoaded } = useGtmCartInfo();
    const domainConfig = useDomainConfig();
    const userContactInformation = useCurrentUserContactInformation();
    const user = useCurrentCustomerData();
    const userConsent = usePersistStore((store) => store.userConsent);

    return useMemo(
        () =>
            getGtmPageViewEvent(
                getGtmPageInfoType(pageType, breadcrumbs),
                gtmCartInfo,
                isCartLoaded,
                user,
                userContactInformation,
                domainConfig,
                userConsent,
            ),
        [pageType, breadcrumbs, gtmCartInfo, isCartLoaded, user, userContactInformation, domainConfig, userConsent],
    );
};

export const useGtmFriendlyPageViewEvent = (
    friendlyUrlPageData: FriendlyUrlPageType | null | undefined,
): GtmPageViewEventType => {
    const { gtmCartInfo, isCartLoaded } = useGtmCartInfo();
    const domainConfig = useDomainConfig();
    const userContactInformation = useCurrentUserContactInformation();
    const user = useCurrentCustomerData();
    const userConsent = usePersistStore((store) => store.userConsent);

    return useMemo(
        () =>
            getGtmPageViewEvent(
                getGtmPageInfoTypeForFriendlyUrl(friendlyUrlPageData),
                gtmCartInfo,
                isCartLoaded,
                user,
                userContactInformation,
                domainConfig,
                userConsent,
            ),
        [friendlyUrlPageData, gtmCartInfo, isCartLoaded, user, userContactInformation, domainConfig, userConsent],
    );
};

const getGtmPageViewEvent = (
    pageInfo: GtmPageInfoType,
    gtmCartInfo: GtmCartInfoType | null,
    isCartLoaded: boolean,
    user: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
    domainConfig: DomainConfigType,
    userConsent: UserConsentFormType | null,
): GtmPageViewEventType => ({
    event: GtmEventType.page_view,
    page: pageInfo,
    user: getGtmUserInfo(user, userContactInformation),
    device: getGtmDeviceType(),
    consent: getGtmConsentInfo(userConsent),
    currencyCode: domainConfig.currencyCode,
    language: domainConfig.defaultLocale,
    cart: gtmCartInfo,
    _clear: true,
    _isLoaded: isCartLoaded,
});

export const getGtmChangeCartItemEvent = (
    event: GtmEventType.add_to_cart | GtmEventType.remove_from_cart,
    cartItem: CartItemFragmentApi,
    listIndex: number | undefined,
    quantity: number,
    currencyCodeCode: string,
    eventValueWithoutVat: number,
    eventValueWithVat: number,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    gtmCartInfo?: GtmCartInfoType | null,
): GtmChangeCartItemEventType => ({
    event,
    ecommerce: {
        listName: gtmProductListName,
        currencyCode: currencyCodeCode,
        valueWithoutVat: eventValueWithoutVat,
        valueWithVat: eventValueWithVat,
        products: [mapGtmCartItemType(cartItem, domainUrl, listIndex, quantity)],
    },
    cart: gtmCartInfo,
    _clear: true,
});

export const getGtmPaymentChangeEvent = (
    gtmCartInfo: GtmCartInfoType,
    updatedPayment: SimplePaymentFragmentApi,
): GtmPaymentChangeEventType => ({
    event: GtmEventType.payment_change,
    ecommerce: {
        valueWithoutVat: gtmCartInfo.valueWithoutVat,
        valueWithVat: gtmCartInfo.valueWithVat,
        products: gtmCartInfo.products ?? [],
        currencyCode: gtmCartInfo.currencyCode,
        paymentType: updatedPayment.name,
        paymentPriceWithoutVat: parseFloat(updatedPayment.price.priceWithoutVat),
        paymentPriceWithVat: parseFloat(updatedPayment.price.priceWithVat),
        promoCodes: gtmCartInfo.promoCodes,
    },
    _clear: true,
});

export const getGtmTransportChangeEvent = (
    gtmCartInfo: GtmCartInfoType,
    updatedTransport: TransportWithAvailablePaymentsAndStoresFragmentApi,
    updatedPickupPlace: ListedStoreFragmentApi | null,
    paymentName: string | undefined,
): GtmTransportChangeEventType => {
    const { transportDetail, transportExtra } = mapGtmShippingInfo(updatedPickupPlace);

    return {
        event: GtmEventType.transport_change,
        ecommerce: {
            valueWithoutVat: gtmCartInfo.valueWithoutVat,
            valueWithVat: gtmCartInfo.valueWithVat,
            products: gtmCartInfo.products ?? [],
            currencyCode: gtmCartInfo.currencyCode,
            promoCodes: gtmCartInfo.promoCodes,
            paymentType: paymentName,
            transportType: updatedTransport.name,
            transportDetail,
            transportExtra,
            transportPriceWithoutVat: parseFloat(updatedTransport.price.priceWithoutVat),
            transportPriceWithVat: parseFloat(updatedTransport.price.priceWithVat),
        },
        _clear: true,
    };
};

export const getGtmProductListViewEvent = (
    products: ListedProductFragmentApi[],
    gtmProductListName: GtmProductListNameType,
    currentPageWithLoadMore: number,
    pageSize: number,
    domainUrl: string,
): GtmProductListViewEventType => ({
    event: GtmEventType.product_list_view,
    ecommerce: {
        listName: gtmProductListName,
        products: products.map((product, index) => {
            const listedProductIndex = (currentPageWithLoadMore - 1) * pageSize + index;

            return mapGtmListedProductType(product, listedProductIndex, domainUrl);
        }),
    },
    _clear: true,
});

export const getGtmShowMessageEvent = (
    type: GtmMessageType,
    message: string,
    detail: GtmMessageDetailType | string,
    origin?: GtmMessageOriginType,
): GtmShowMessageEventType => ({
    event: GtmEventType.show_message,
    eventParameters: {
        type,
        origin,
        detail,
        message,
    },
    _clear: true,
});

export const getGtmConsentUpdateEvent = (updatedGtmConsentInfo: GtmConsentInfoType): GtmConsentUpdateEventType => ({
    event: GtmEventType.consent_update,
    consent: updatedGtmConsentInfo,
    _clear: true,
});
