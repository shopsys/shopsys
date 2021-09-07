import getConfig from 'next/config';

export const useGetInternationalizedStaticUrls = (urls: string[], domainUrl: string): string[] => {
    const { publicRuntimeConfig } = getConfig();
    return urls.map((url) => publicRuntimeConfig.availableStaticUrls[domainUrl][url]);
};
