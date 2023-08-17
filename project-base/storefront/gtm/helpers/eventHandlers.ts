import {
    getGtmAutocompleteResultClickEvent,
    getGtmChangeCartItemEvent,
    getGtmConsentUpdateEvent,
    getGtmCreateOrderEvent,
    getGtmPaymentChangeEvent,
    getGtmPaymentFailEvent,
    getGtmProductClickEvent,
    getGtmSendFormEvent,
    getGtmShowMessageEvent,
    getGtmTransportChangeEvent,
} from './eventFactories';
import { gtmSafePushEvent } from './gtm';
import {
    GtmEventType,
    GtmFormType,
    GtmMessageDetailType,
    GtmMessageOriginType,
    GtmMessageType,
    GtmProductListNameType,
    GtmSectionType,
} from 'gtm/types/enums';
import { GtmCreateOrderEventOrderPartType } from 'gtm/types/events';
import { GtmCartInfoType, GtmConsentInfoType, GtmUserInfoType } from 'gtm/types/objects';
import { CartItemFragmentApi } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { SimplePaymentFragmentApi } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { ListedProductFragmentApi } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { SimpleProductFragmentApi } from 'graphql/requests/products/fragments/SimpleProductFragment.generated';
import { ListedStoreFragmentApi } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';

export const onGtmCreateOrderEventHandler = (
    gtmCreateOrderEventOrderPart: GtmCreateOrderEventOrderPartType | undefined,
    gtmCreateOrderEventUserPart: GtmUserInfoType | undefined,
    isPaymentSuccessful?: boolean,
): void => {
    if (gtmCreateOrderEventOrderPart === undefined || gtmCreateOrderEventUserPart === undefined) {
        return;
    }

    const gtmCreateOrderEvent = getGtmCreateOrderEvent(
        gtmCreateOrderEventOrderPart,
        gtmCreateOrderEventUserPart,
        isPaymentSuccessful,
    );

    gtmSafePushEvent(gtmCreateOrderEvent);
};

export const onGtmPaymentFailEventHandler = (orderId: string): void => {
    gtmSafePushEvent(getGtmPaymentFailEvent(orderId));
};

export const onGtmSendFormEventHandler = (form: GtmFormType): void => {
    gtmSafePushEvent(getGtmSendFormEvent(form));
};

export const onGtmProductClickEventHandler = (
    product: ListedProductFragmentApi | SimpleProductFragmentApi,
    gtmProductListName: GtmProductListNameType,
    index: number,
    domainUrl: string,
): void => {
    gtmSafePushEvent(getGtmProductClickEvent(product, gtmProductListName, index, domainUrl));
};

export const onGtmAutocompleteResultClickEventHandler = (
    keyword: string,
    section: GtmSectionType,
    itemName: string,
): void => {
    gtmSafePushEvent(getGtmAutocompleteResultClickEvent(keyword, section, itemName));
};

export const onGtmRemoveFromCartEventHandler = (
    removedCartItem: CartItemFragmentApi,
    currencyCode: string,
    eventValueWithoutVat: number,
    eventValueWithVat: number,
    listIndex: number,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    gtmCartInfo?: GtmCartInfoType | null,
): void => {
    gtmSafePushEvent(
        getGtmChangeCartItemEvent(
            GtmEventType.remove_from_cart,
            removedCartItem,
            listIndex,
            removedCartItem.quantity,
            currencyCode,
            eventValueWithoutVat,
            eventValueWithVat,
            gtmProductListName,
            domainUrl,
            gtmCartInfo,
        ),
    );
};

export const onGtmChangeCartItemEventHandler = (
    addedCartItem: CartItemFragmentApi,
    currencyCode: string,
    eventValueWithoutVat: number,
    eventValueWithVat: number,
    listIndex: number | undefined,
    quantityDifference: number,
    gtmProductListName: GtmProductListNameType,
    domainUrl: string,
    gtmCartInfo?: GtmCartInfoType | null,
): void => {
    const absoluteQuantity = Math.abs(quantityDifference);
    const event = getGtmChangeCartItemEvent(
        GtmEventType.add_to_cart,
        addedCartItem,
        listIndex,
        absoluteQuantity,
        currencyCode,
        eventValueWithoutVat,
        eventValueWithVat,
        gtmProductListName,
        domainUrl,
        gtmCartInfo,
    );

    if (quantityDifference < 0) {
        event.event = GtmEventType.remove_from_cart;
    }

    gtmSafePushEvent(event);
};

export const onGtmPaymentChangeEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedPayment: SimplePaymentFragmentApi | null,
): void => {
    if (gtmCartInfo && updatedPayment !== null) {
        gtmSafePushEvent(getGtmPaymentChangeEvent(gtmCartInfo, updatedPayment));
    }
};

export const onGtmTransportChangeEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedTransport: TransportWithAvailablePaymentsAndStoresFragmentApi | null,
    updatedPickupPlace: ListedStoreFragmentApi | null,
    paymentName: string | undefined,
): void => {
    if (gtmCartInfo && updatedTransport !== null) {
        gtmSafePushEvent(getGtmTransportChangeEvent(gtmCartInfo, updatedTransport, updatedPickupPlace, paymentName));
    }
};

export const onGtmShowMessageEventHandler = (
    type: GtmMessageType,
    message: string,
    detail: GtmMessageDetailType | string,
    origin?: GtmMessageOriginType,
): void => {
    gtmSafePushEvent(getGtmShowMessageEvent(type, message, detail, origin));
};

export const onGtmConsentUpdateEventHandler = (gtmConsentInfo: GtmConsentInfoType): void => {
    gtmSafePushEvent(getGtmConsentUpdateEvent(gtmConsentInfo));
};
