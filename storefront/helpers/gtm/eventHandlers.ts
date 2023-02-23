import {
    getGtmChangeCartItemEvent,
    getGtmPaymentInfoEvent,
    getGtmProductDetailOnClickEvent,
    getGtmSearchClickEvent,
    getGtmShippingInfoEvent,
    getNewGtmEcommerceEvent,
} from './eventFactories';
import { getGtmPurchaseData, gtmSafePushEvent } from './gtm';
import {
    CartFragmentApi,
    CartItemFragmentApi,
    ListedProductFragmentApi,
    ListedStoreFragmentApi,
    SimplePaymentFragmentApi,
    SimpleProductFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
import { DomainConfigType } from 'helpers/domain/domain';
import { GtmCartInfoType, GtmConsentInfoType, GtmConsentUpdateType, GtmListNameType, GtmSectionType } from 'types/gtm';

export const onClickProductDetailGtmEventHandler = (
    product: ListedProductFragmentApi | SimpleProductFragmentApi,
    listName: GtmListNameType,
    index: number,
    domainUrl: string,
): void => {
    const event = getNewGtmEcommerceEvent('ec.product_click', true);
    event.ecommerce = getGtmProductDetailOnClickEvent(product, listName, index, domainUrl);
    gtmSafePushEvent(event);
};

export const onRemoveCartItemGtmEventHandler = (
    removedCartItem: CartItemFragmentApi,
    currencyCode: string,
    eventValue: number,
    eventValueWithTax: number,
    listIndex: number,
    listName: GtmListNameType,
    domainUrl: string,
): void => {
    const event = getNewGtmEcommerceEvent('ec.remove_from_cart', true);
    event.ecommerce = getGtmChangeCartItemEvent(
        removedCartItem,
        removedCartItem.quantity,
        currencyCode,
        eventValue,
        eventValueWithTax,
        listName,
        domainUrl,
        listIndex,
    );
    gtmSafePushEvent(event);
};

export const onChangeCartItemGtmEventHandler = (
    addedCartItem: CartItemFragmentApi,
    currencyCode: string,
    eventValue: number,
    eventValueWithTax: number,
    quantityDifference: number,
    listName: GtmListNameType,
    domainUrl: string,
    listIndex?: number,
): void => {
    const event = getNewGtmEcommerceEvent('ec.add_to_cart', true);
    if (quantityDifference < 0) {
        event.event = 'ec.remove_from_cart';
    }
    const absoluteQuantity = Math.abs(quantityDifference);
    event.ecommerce = getGtmChangeCartItemEvent(
        addedCartItem,
        absoluteQuantity,
        currencyCode,
        eventValue,
        eventValueWithTax,
        listName,
        domainUrl,
        listIndex,
    );
    gtmSafePushEvent(event);
};

export const onTransportChangeGtmEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedTransport: TransportWithAvailablePaymentsAndStoresFragmentApi | null,
    updatedPickupPlace: ListedStoreFragmentApi | null,
    updatedPaymentName: string | undefined,
): void => {
    if (gtmCartInfo && updatedTransport !== null) {
        const event = getNewGtmEcommerceEvent('ec.shipping_info', true);
        event.ecommerce = getGtmShippingInfoEvent(
            gtmCartInfo,
            updatedTransport,
            updatedPickupPlace,
            updatedPaymentName,
        );
        gtmSafePushEvent(event);
    }
};

export const onPaymentChangeGtmEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedPayment: SimplePaymentFragmentApi | null,
): void => {
    if (gtmCartInfo && updatedPayment !== null) {
        const event = getNewGtmEcommerceEvent('ec.payment_info', true);
        event.ecommerce = getGtmPaymentInfoEvent(gtmCartInfo, updatedPayment);
        gtmSafePushEvent(event);
    }
};

export const onPurchaseOrderGtmEventHandler = (
    cart: CartFragmentApi,
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi,
    pickupPlace: ListedStoreFragmentApi | null,
    payment: SimplePaymentFragmentApi,
    promoCode: string | null,
    orderNumber: string,
    domainConfig: DomainConfigType,
): void => {
    const event = getNewGtmEcommerceEvent('ec.purchase', true);
    event.ecommerce = getGtmPurchaseData(cart, transport, pickupPlace, payment, promoCode, orderNumber, domainConfig);
    gtmSafePushEvent(event);
};

export const onClickSuggestResultGtmEventHandler = (
    keyword: string,
    section: GtmSectionType,
    itemName: string,
): void => {
    const event = getGtmSearchClickEvent(keyword, section, itemName);
    gtmSafePushEvent(event);
};

export const onConsentUpdateGtmEventHandler = (updatedConsent: GtmConsentInfoType): void => {
    const event: GtmConsentUpdateType = {
        event: 'consent.update',
        consent: updatedConsent,
    };
    gtmSafePushEvent(event);
};
