import { desktopFirstSizes, mobileFirstSizes } from 'components/Theme/mediaQueries';
import { canUseDom } from 'helpers/canUseDom';
import { GtmDeviceTypes as GtmDeviceType } from 'types/gtm';

export const GTM_ID = process.env.NEXT_PUBLIC_GOOGLE_TAG_MANAGER_ID;

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
