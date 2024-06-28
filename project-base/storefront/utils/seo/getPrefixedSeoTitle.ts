export const getPrefixedSeoTitle = (title: string | null | undefined, prefix: string | null | undefined) => {
    if (!title) {
        return null;
    }

    return prefix ? `${prefix} ${title}` : title;
};
