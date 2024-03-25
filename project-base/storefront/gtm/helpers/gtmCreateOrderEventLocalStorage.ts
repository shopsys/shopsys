import { compressObjectToString, decompressStringToObject } from './objectCompression';
import { GtmCreateOrderEventOrderPartType } from 'gtm/types/events';
import { GtmUserInfoType } from 'gtm/types/objects';
import { isClient } from 'helpers/isClient';

const GTM_CREATE_ORDER_OBJECT_LOCAL_STORAGE_KEY = 'gtmCreateOrderEvent' as const;

export const saveGtmCreateOrderEventInLocalStorage = (
    gtmCreateOrderEventOrderPart: GtmCreateOrderEventOrderPartType,
    gtmCreateOrderEventUserPart: GtmUserInfoType,
): void => {
    if (!isClient) {
        return;
    }
    const stringifiedGtmCreateOrderEvent = JSON.stringify({
        gtmCreateOrderEventOrderPart: compressObjectToString(gtmCreateOrderEventOrderPart),
        gtmCreateOrderEventUserPart: compressObjectToString(gtmCreateOrderEventUserPart),
    });

    localStorage.setItem(GTM_CREATE_ORDER_OBJECT_LOCAL_STORAGE_KEY, stringifiedGtmCreateOrderEvent);
};

export const getGtmCreateOrderEventFromLocalStorage = (): {
    gtmCreateOrderEventOrderPart: GtmCreateOrderEventOrderPartType | undefined;
    gtmCreateOrderEventUserPart: GtmUserInfoType | undefined;
} => {
    if (!isClient) {
        return {
            gtmCreateOrderEventOrderPart: undefined,
            gtmCreateOrderEventUserPart: undefined,
        };
    }

    const stringifiedGtmCreateOrderEvent = localStorage.getItem(GTM_CREATE_ORDER_OBJECT_LOCAL_STORAGE_KEY);

    if (stringifiedGtmCreateOrderEvent === null) {
        return {
            gtmCreateOrderEventOrderPart: undefined,
            gtmCreateOrderEventUserPart: undefined,
        };
    }

    const parsedGtmCreateOrderEvent = JSON.parse(stringifiedGtmCreateOrderEvent);

    return {
        gtmCreateOrderEventOrderPart: decompressStringToObject(parsedGtmCreateOrderEvent.gtmCreateOrderEventOrderPart),
        gtmCreateOrderEventUserPart: decompressStringToObject(parsedGtmCreateOrderEvent.gtmCreateOrderEventUserPart),
    };
};

export const removeGtmCreateOrderEventFromLocalStorage = (): void => {
    if (!isClient) {
        return;
    }

    localStorage.removeItem(GTM_CREATE_ORDER_OBJECT_LOCAL_STORAGE_KEY);
};
