import { desktopFirstSizes, mobileFirstSizes } from 'components/Theme/mediaQueries';
import { canUseDom } from 'helpers/misc/canUseDom';
import { GtmDeviceTypes as GtmDeviceType } from 'types/gtm';
import { v4 as uuidV4 } from 'uuid';

export const getGtmDeviceType = (): GtmDeviceType => {
    if (typeof navigator === 'undefined') {
        return 'unknown';
    }
    if (canUseDom()) {
        if (window.innerWidth <= desktopFirstSizes.mobile) {
            return 'mobile';
        }
        return window.innerWidth >= mobileFirstSizes.vl ? 'desktop' : 'tablet';
    }

    return 'unknown';
};

export const getRandomPageId = (): string => uuidV4();
