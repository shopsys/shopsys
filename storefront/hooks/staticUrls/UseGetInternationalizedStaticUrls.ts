import getConfig from 'next/config';

export const useGetInternationalizedStaticUrls = (
    urls: (string | { url: string; param: string | undefined })[],
    domainUrl: string,
): string[] => {
    const { publicRuntimeConfig } = getConfig();

    return urls.map((url) => {
        if (typeof url === 'string') {
            return publicRuntimeConfig.availableStaticUrls[domainUrl][url];
        }

        const staticUrlTemplate = publicRuntimeConfig.availableStaticUrls[domainUrl][url.url];
        const staticPart = staticUrlTemplate.split(':')[0];

        return staticPart + url.param ?? '';
    });
};
