import { IncomingHttpHeaders } from 'node:http';

export const isFullPageRequest = (requestHeaders: IncomingHttpHeaders): boolean => {
    return requestHeaders['x-nextjs-data'] !== '1';
};
