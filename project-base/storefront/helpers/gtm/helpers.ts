import { desktopFirstSizes, mobileFirstSizes } from 'components/Theme/mediaQueries';
import { canUseDom } from 'helpers/misc/canUseDom';
import { isServer } from 'helpers/misc/isServer';
import { compressToEncodedURIComponent, decompressFromEncodedURIComponent } from 'lz-string';
import { GtmDeviceTypes } from 'types/gtm/enums';
import { GtmCreateOrderEventOrderPartType } from 'types/gtm/events';
import { GtmUserInfoType } from 'types/gtm/objects';
import { v4 as uuidV4 } from 'uuid';

const GTM_CREATE_ORDER_OBJECT_LOCAL_STORAGE_KEY = 'gtmCreateOrderEvent' as const;

export const getGtmDeviceType = (): GtmDeviceTypes => {
    if (typeof navigator === 'undefined') {
        return GtmDeviceTypes.unknown;
    }
    if (canUseDom()) {
        if (window.innerWidth <= desktopFirstSizes.mobile) {
            return GtmDeviceTypes.mobile;
        }
        return window.innerWidth >= mobileFirstSizes.vl ? GtmDeviceTypes.desktop : GtmDeviceTypes.tablet;
    }

    return GtmDeviceTypes.unknown;
};

export const getRandomPageId = (): string => uuidV4();

export const compressObjectToString = (object: Record<string, unknown>): string =>
    compressToEncodedURIComponent(JSON.stringify(object));

export const decompressStringToObject = <T>(string: string | undefined): T | undefined => {
    if (!string) {
        return undefined;
    }

    const decompressedString = decompressFromEncodedURIComponent(string);

    return JSON.parse(decompressedString);
};

export const saveGtmCreateOrderEventInLocalStorage = (
    gtmCreateOrderEventOrderPart: GtmCreateOrderEventOrderPartType,
    gtmCreateOrderEventUserPart: GtmUserInfoType,
): void => {
    if (isServer()) {
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
    if (isServer()) {
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
    if (isServer()) {
        return;
    }

    localStorage.removeItem(GTM_CREATE_ORDER_OBJECT_LOCAL_STORAGE_KEY);
};
