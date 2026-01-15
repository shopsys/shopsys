import { routes } from './routes';
import { getNextConfig } from 'utils/config/getNextConfig';

const nextConfig = getNextConfig();

export const STATIC_REWRITE_PATHS = {
    [(nextConfig?.publicRuntimeConfig?.domains?.[0]?.url || process.env.DOMAIN_HOSTNAME_1) as string]: routes[0],
    [(nextConfig?.publicRuntimeConfig?.domains?.[1]?.url || process.env.DOMAIN_HOSTNAME_2) as string]: routes[1],
    [(nextConfig?.publicRuntimeConfig?.domains?.[2]?.url || process.env.DOMAIN_HOSTNAME_3) as string]: routes[2],
} as const;

export type StaticRewritePathKeyType = keyof (typeof STATIC_REWRITE_PATHS)[string];

const isStaticRewritePath = (path: string, domainUrl: string) => {
    return path in STATIC_REWRITE_PATHS[domainUrl];
};

export const getStaticRewritePathKeyType = (path: string, domainUrl: string) => {
    if (!isStaticRewritePath(path, domainUrl)) {
        return null;
    }
    return path as StaticRewritePathKeyType;
};
