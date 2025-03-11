import { routes } from 'config/routes';
import 'server-only';

export const STATIC_REWRITE_PATHS = {
    [process.env.DOMAIN_HOSTNAME_1 as string]: routes[0],
    [process.env.DOMAIN_HOSTNAME_2 as string]: routes[1],
} as const;

export type StaticRewritePathKeyType = keyof (typeof STATIC_REWRITE_PATHS)[string];
