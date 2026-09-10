export const getDocumentTitle = (title: string, titleSuffix: string): string =>
    [title, titleSuffix].filter(Boolean).join(' ');
