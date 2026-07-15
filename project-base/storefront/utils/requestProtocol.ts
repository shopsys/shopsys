import { logException } from './errors/logException';

type Protocol = 'http' | 'https';

const getProtocolClientSide = (): Protocol => {
    if (typeof window === 'undefined') {
        throw new Error('getProtocolClientSide must be called on the client side');
    }

    return window.location.protocol === 'https:' ? 'https' : 'http';
};

export const getIsHttps = (protocol?: string | undefined): boolean | undefined => {
    if (!protocol) {
        try {
            return getProtocolClientSide() === 'https';
        } catch {
            logException('protocol must be provided when running on the server side');
            return undefined;
        }
    }

    return protocol === 'https';
};
