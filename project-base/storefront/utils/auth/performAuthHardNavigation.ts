export const performAuthHardNavigation = (url?: string) => {
    if (url === undefined) {
        window.location.reload();

        return;
    }

    window.location.replace(getSafeSameOriginUrl(url));
};

const getSafeSameOriginUrl = (url: string): string => {
    try {
        const urlObject = new URL(url, window.location.origin);

        if (urlObject.origin !== window.location.origin || /^\/[/\\]/.test(urlObject.pathname)) {
            return '/';
        }

        return `${window.location.origin}${urlObject.pathname}${urlObject.search}${urlObject.hash}`;
    } catch {
        return '/';
    }
};
