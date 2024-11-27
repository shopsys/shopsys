export const getUrlWithoutGetParameters = (originalUrl: string | undefined | null): string => {
    return originalUrl?.split(/(\?|#)/)[0] || '';
};
