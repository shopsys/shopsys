import { NextIncomingMessage } from 'next/dist/server/request-meta';
import { canUseDom } from './canUseDom';

export const isServer = (): boolean => !canUseDom();

export const isRedirectedFromSsr = (requestHeader: NextIncomingMessage['headers']): boolean => {
    return requestHeader['x-nextjs-data'] !== '1';
};
