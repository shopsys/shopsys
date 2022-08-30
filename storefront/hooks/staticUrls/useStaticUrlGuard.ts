import getConfig from 'next/config';

export const useStaticUrlGuard = (path: string, domainUrl: string): boolean => {
    const { publicRuntimeConfig } = getConfig();
    const pathSegments = path.split('/');

    for (const key in publicRuntimeConfig.availableStaticUrls[domainUrl]) {
        const rewriteKey = publicRuntimeConfig.availableStaticUrls[domainUrl][key];

        if (typeof rewriteKey !== 'string') {
            continue;
        }

        const staticUrlSegments = rewriteKey.split('/');
        if (
            pathSegments.every((pathSegment, index) => {
                return staticUrlSegments[index] === pathSegment || staticUrlSegments[index]?.charAt(0) === ':';
            })
        ) {
            return true;
        }
    }

    return false;
};
