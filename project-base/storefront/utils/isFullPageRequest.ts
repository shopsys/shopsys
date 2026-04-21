import { NextIncomingMessage } from 'next/dist/server/request-meta';

export const isFullPageRequest = (requestHeaders: NextIncomingMessage['headers']): boolean => {
    return requestHeaders['x-nextjs-data'] !== '1';
};
