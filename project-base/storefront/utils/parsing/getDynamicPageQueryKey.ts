export const getDynamicPageQueryKey = (pathname: string) => {
    const start = pathname.indexOf('[');
    const end = pathname.indexOf(']');

    if (start !== -1 && end !== -1) {
        return pathname.substring(start + 1, end);
    }

    return undefined;
};
