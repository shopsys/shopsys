import { NextIncomingMessage } from 'next/dist/server/request-meta';

export const getIsRedirectedFromSsr = (requestHeader: NextIncomingMessage['headers']): boolean => {
    return requestHeader['x-nextjs-data'] !== '1';
};
