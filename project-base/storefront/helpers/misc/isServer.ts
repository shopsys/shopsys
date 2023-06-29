import { NextIncomingMessage } from 'next/dist/server/request-meta';

export const isServer = (): boolean =>
    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
    !(
        typeof window !== 'undefined' &&
        typeof window.document === 'object' &&
        typeof window.document.createElement === 'function'
    );

export const isRedirectedFromSsr = (requestHeader: NextIncomingMessage['headers']): boolean => {
    return requestHeader['x-nextjs-data'] !== '1';
};
