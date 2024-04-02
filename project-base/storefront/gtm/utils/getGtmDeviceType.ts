import { GtmDeviceTypes } from 'gtm/enums/GtmDeviceTypes';
import { isClient } from 'utils/isClient';
import { desktopFirstSizes, mobileFirstSizes } from 'utils/mediaQueries';

export const getGtmDeviceType = (): GtmDeviceTypes => {
    if (!isClient) {
        return GtmDeviceTypes.unknown;
    }
    if (window.innerWidth <= desktopFirstSizes.mobile) {
        return GtmDeviceTypes.mobile;
    }
    return window.innerWidth >= mobileFirstSizes.vl ? GtmDeviceTypes.desktop : GtmDeviceTypes.tablet;
};
