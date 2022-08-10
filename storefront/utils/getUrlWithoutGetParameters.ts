export const getUrlWithoutGetParameters = (originalUrl: string): string => {
    return originalUrl.split(/(\?|#)/)[0];
};
