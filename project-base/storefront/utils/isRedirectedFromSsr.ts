import { NextIncomingMessage } from 'next/dist/server/request-meta';

export const isRedirectedFromSsr = (requestHeader: NextIncomingMessage['headers']): boolean => {
    return requestHeader['x-nextjs-data'] !== '1';
};
