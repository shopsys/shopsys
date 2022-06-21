import { desktopFirstSizes, mobileFirstSizes } from 'components/Theme/mediaQueries';
import { canUseDom } from 'helpers/canUseDom';
import getConfig from 'next/config';
import { GtmDeviceTypes as GtmDeviceType } from 'types/gtm';

const { publicRuntimeConfig } = getConfig();

export const GTM_ID = publicRuntimeConfig.gtmId;

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
