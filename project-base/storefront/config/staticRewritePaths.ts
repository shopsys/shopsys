import { routes } from './routes';
import { getPublicConfigProperty } from 'envConfig';

const domains = getPublicConfigProperty('domains');

export const STATIC_REWRITE_PATHS = {
    [domains[0].url]: routes[0],
    [domains[1].url]: routes[1],
    [domains[2].url]: routes[2],
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
