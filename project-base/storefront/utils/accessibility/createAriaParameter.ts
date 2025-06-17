export const createAriaParameter = (baseTitle: string, title: string): string => {
    return `${baseTitle}-${title
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^a-z0-9-]/g, '')}`;
};
