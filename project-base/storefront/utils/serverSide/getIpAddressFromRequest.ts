import { IncomingHttpHeaders } from 'node:http';
import { isIP } from 'node:net';

type RequestWithIpAddress = {
    headers: IncomingHttpHeaders;
    socket?: {
        remoteAddress?: string;
    };
};

export const getIpAddressFromRequest = (request: RequestWithIpAddress | undefined): string | undefined => {
    const xForwardedFor = request?.headers['x-forwarded-for'];
    const rawXForwardedFor = Array.isArray(xForwardedFor) ? xForwardedFor.join(',') : xForwardedFor;
    const forwardedIpAddresses = rawXForwardedFor
        ?.split(',')
        .map((ipAddress) => ipAddress.trim())
        .filter(Boolean);
    const forwardedIpAddress = forwardedIpAddresses?.[forwardedIpAddresses.length - 1];

    if (forwardedIpAddress !== undefined && isIP(forwardedIpAddress)) {
        return forwardedIpAddress;
    }

    const remoteAddress = request?.socket?.remoteAddress;

    return remoteAddress !== undefined && isIP(remoteAddress) ? remoteAddress : undefined;
};
