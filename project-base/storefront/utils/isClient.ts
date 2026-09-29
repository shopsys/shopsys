export const isClient = !!(
    typeof window !== 'undefined' &&
    window !== null &&
    typeof window.document === 'object' &&
    typeof window.document.createElement === 'function'
);
