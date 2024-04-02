// some browser versions do not support the string.replaceAll() method
export const replaceAll = (string: string, search: string | RegExp, replace: string): string => {
    return string.split(search).join(replace);
};
