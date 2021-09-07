import getConfig from 'next/config';

export const useStaticUrlGuard = (path: string, domainUrl: string): boolean => {
    const { publicRuntimeConfig } = getConfig();

    for (const key in publicRuntimeConfig.availableStaticUrls[domainUrl]) {
        if (publicRuntimeConfig.availableStaticUrls[domainUrl][key] === path) {
            return true;
        }
    }

    return false;
};
