export const getStringWithoutLeadingSlash = (string: string): string =>
    string.startsWith('/') ? string.slice(1) : string;

export const getStringWithoutTrailingSlash = (string: string): string =>
    string.endsWith('/') ? string.slice(0, -1) : string;
