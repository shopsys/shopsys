import { getPublicConfigProperty } from 'envConfig';
import { routes } from './routes';

const domains = getPublicConfigProperty('domains');

export const STATIC_REWRITE_PATHS = {
    [domains[0].url]: routes[0],
    [domains[1].url]: routes[1],
    [domains[2].url]: routes[2],
} as const;

export type StaticRewritePathKeyType = keyof (typeof STATIC_REWRITE_PATHS)[string];
