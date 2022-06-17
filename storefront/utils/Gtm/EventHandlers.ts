import {
    getGtmChangeCartItemEvent,
    getGtmPaymentInfoEvent,
    getGtmProductDetailOnClickEvent,
    getGtmSearchClickEvent,
    getGtmShippingInfoEvent,
    getNewGtmEcommerceEvent,
} from './EventFactories';
import { getGtmPurchaseData, gtmSafePushEvent } from './Gtm';
import { mapPayment } from 'connectors/payments/Payment';
import { mapTransport } from 'connectors/transports/Transports';
import { SimplePaymentFragmentApi, TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';
import { CartItemType, CartType } from 'types/cart';
import { GtmCartInfoType, GtmConsentInfoType, GtmConsentUpdateType, GtmListNameType, GtmSectionType } from 'types/gtm';
import { PaymentType } from 'types/payment';
import { PickupPlaceType } from 'types/pickupPlace';
import { ListedProductType, SimpleProductType } from 'types/product';
import { TransportType } from 'types/transport';

export const onClickProductDetailGtmEventHandler = (
    product: ListedProductType | SimpleProductType,
    listName: GtmListNameType,
    index: number,
): void => {
    const event = getNewGtmEcommerceEvent('ec.product_click', true);
    event.ecommerce = getGtmProductDetailOnClickEvent(product, listName, index);
    gtmSafePushEvent(event);
};

export const onRemoveCartItemGtmEventHandler = (
    removedCartItem: CartItemType,
    listIndex: number,
    listName: GtmListNameType,
): void => {
    const event = getNewGtmEcommerceEvent('ec.remove_from_cart', true);
    event.ecommerce = getGtmChangeCartItemEvent(removedCartItem, listIndex, removedCartItem.quantity, listName);
    gtmSafePushEvent(event);
};

export const onChangeCartItemGtmEventHandler = (
    addedCartItem: CartItemType,
    listIndex: number,
    quantityDifference: number,
    listName: GtmListNameType,
): void => {
    const event = getNewGtmEcommerceEvent('ec.add_to_cart', true);
    if (quantityDifference < 0) {
        event.event = 'ec.remove_from_cart';
    }
    const absoluteQuantity = Math.abs(quantityDifference);
    event.ecommerce = getGtmChangeCartItemEvent(addedCartItem, listIndex, absoluteQuantity, listName);
    gtmSafePushEvent(event);
};

export const onTransportChangeGtmEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedTransport: TransportWithAvailablePaymentsAndStoresFragmentApi | null,
    updatedPickupPlace: PickupPlaceType | null,
    updatedPaymentName: string | undefined,
    currencyCode: string,
): void => {
    if (gtmCartInfo && updatedTransport !== null) {
        const event = getNewGtmEcommerceEvent('ec.shipping_info', true);
        const mappedTransport = mapTransport(updatedTransport, currencyCode);
        event.ecommerce = getGtmShippingInfoEvent(gtmCartInfo, mappedTransport, updatedPickupPlace, updatedPaymentName);
        gtmSafePushEvent(event);
    }
};

export const onPaymentChangeGtmEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedPayment: SimplePaymentFragmentApi | null,
    currencyCode: string,
): void => {
    if (gtmCartInfo && updatedPayment !== null) {
        const event = getNewGtmEcommerceEvent('ec.payment_info', true);
        const mappedPayment = mapPayment(updatedPayment, currencyCode);
        event.ecommerce = getGtmPaymentInfoEvent(gtmCartInfo, mappedPayment);
        gtmSafePushEvent(event);
    }
};

export const onPurchaseOrderGtmEventHandler = (
    cart: CartType,
    transport: TransportType,
    pickupPlace: PickupPlaceType | null,
    payment: PaymentType,
    promoCode: string | null,
    orderNumber: string,
): void => {
    const event = getNewGtmEcommerceEvent('ec.purchase', true);
    event.ecommerce = getGtmPurchaseData(cart, transport, pickupPlace, payment, promoCode, orderNumber);
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
