export const getUrlWithoutGetParameters = (originalUrl: string | undefined): string => {
    return originalUrl?.split(/(\?|#)/)[0] || '';
};
