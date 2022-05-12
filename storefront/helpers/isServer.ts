export const isServer = (): boolean =>
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
    !(
        typeof window !== 'undefined' &&
        typeof window.document === 'object' &&
        typeof window.document.createElement === 'function'
    );
