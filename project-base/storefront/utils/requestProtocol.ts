import { getDomainConfig } from './domain/domainConfig';
import { logException } from './errors/logException';
import { GetServerSidePropsContext, NextPageContext } from 'next';

type Protocol = 'http' | 'https';

export const getProtocolClientSide = (): Protocol => {
    if (typeof window === 'undefined') {
        throw new Error('getProtocolClientSide must be called on the client side');
    }

    return window.location.protocol === 'https:' ? 'https' : 'http';
};

export const getProtocol = (context: GetServerSidePropsContext | NextPageContext | undefined): Protocol | undefined => {
    if (!context) {
        try {
            return getProtocolClientSide();
        } catch {
            logException('context must be provided when running on the server side');
            return undefined;
        }
    }

    const host = context.req?.headers.host;

    if (!host) {
        throw new Error('host was not found in the request headers');
    }

    const domainConfig = getDomainConfig(host);
    const protocol = domainConfig.url.split('://')[0];

    if (protocol !== 'http' && protocol !== 'https') {
        throw new Error('protocol must be http or https');
    }

    return protocol;
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
