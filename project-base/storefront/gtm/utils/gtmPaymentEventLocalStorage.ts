import { compressObjectToString, decompressStringToObject } from './objectCompression';
import { GtmPaymentEventType } from 'gtm/types/events';
import { isClient } from 'utils/isClient';

const GTM_PAYMENT_OBJECT_LOCAL_STORAGE_KEY = 'gtmPaymentEvent' as const;

export const saveGtmPaymentEventInLocalStorage = (gtmPaymentEventOrder: GtmPaymentEventType): void => {
    if (!isClient) {
        return;
    }
    const stringifiedGtmCreateOrderEvent = JSON.stringify(compressObjectToString(gtmPaymentEventOrder));

    localStorage.setItem(GTM_PAYMENT_OBJECT_LOCAL_STORAGE_KEY, stringifiedGtmCreateOrderEvent);
};

export const getGtmPaymentEventFromLocalStorage = (): {
    gtmPaymentEvent: GtmPaymentEventType | undefined;
} => {
    if (!isClient) {
        return { gtmPaymentEvent: undefined };
    }

    const stringifiedGtmPaymentEvent = localStorage.getItem(GTM_PAYMENT_OBJECT_LOCAL_STORAGE_KEY);

    if (stringifiedGtmPaymentEvent === null) {
        return { gtmPaymentEvent: undefined };
    }

    const parsedGtmPaymentEvent = JSON.parse(stringifiedGtmPaymentEvent);

    return {
        gtmPaymentEvent: decompressStringToObject(parsedGtmPaymentEvent),
    };
};

export const removeGtmPaymentEventFromLocalStorage = (): void => {
    if (!isClient) {
        return;
    }

    localStorage.removeItem(GTM_PAYMENT_OBJECT_LOCAL_STORAGE_KEY);
};
