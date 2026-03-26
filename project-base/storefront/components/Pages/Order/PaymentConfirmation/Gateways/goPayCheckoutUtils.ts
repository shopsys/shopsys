/**
 * Checks whether a URL returned by GoPay's checkout callback points back to our storefront.
 * Internal URLs indicate user actions inside the iframe (e.g. "Back to shop"),
 * while external URLs indicate 3DS redirects handled by GoPay itself.
 */
export const isInternalGoPayReturnUrl = (checkoutResultUrl: string): boolean => {
    if (typeof window === 'undefined') {
        return false;
    }

    try {
        const parsedUrl = new URL(checkoutResultUrl, window.location.href);

        return parsedUrl.origin === window.location.origin;
    } catch {
        return false;
    }
};
