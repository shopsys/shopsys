import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';

export const getSlugFromUrl = (originalUrl: string): string => {
    return getUrlWithoutGetParameters(originalUrl).replace('/', '');
};

export const getSlugFromServerSideUrl = (originalUrl: string): string => {
    const lastUrlSegment = originalUrl.split('/').pop()!;
    const beforeExtensionSegment = lastUrlSegment.split('.')[0];
    const strippedSlug = beforeExtensionSegment.split('?')[0];

    return strippedSlug;
};
