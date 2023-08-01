import { captureException } from '@sentry/nextjs';
import md5 from 'crypto-js/md5';
import { isServer } from 'helpers/misc/isServer';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';

const CACHE_REGEXP = `@_redisCache\\(\\s?ttl:\\s?([0-9]*)\\s?\\)`;
const QUERY_NAME_REGEXP = `query\\s([A-z]*)(\\([A-z:!0-9$,\\s]*\\))?\\s@_redisCache`;
const REDIS_PREFIX_PATTERN = `${process.env.REDIS_PREFIX}:fe:queryCache:`;

export const fetcher =
    (redisClient: RedisClientType<RedisModules, RedisScripts>) =>
    async (input: URL | RequestInfo, init?: RequestInit | undefined): Promise<Response> => {
        if (!isServer() || !init || process.env.GRAPHQL_REDIS_CACHE !== '1') {
            return fetch(input, init);
        }

        try {
            if (typeof init.body !== 'string' || !init.body.match(CACHE_REGEXP)) {
                return fetch(input, init);
            }
            const [, rawTtl] = init.body.match(CACHE_REGEXP) as string[];
            const ttl = parseInt(rawTtl, 10);

            if (ttl <= 0) {
                return fetch(input, init);
            }

            const host = (init.headers ? new Headers(init.headers) : new Headers()).get('OriginalHost');
            const [, queryName] = init.body.match(QUERY_NAME_REGEXP) ?? [];
            const hash = `${REDIS_PREFIX_PATTERN}${queryName}:${host}:${md5(init.body).toString().substring(0, 7)}`;

            const fromCache = await redisClient.get(hash);
            if (fromCache !== null) {
                const response = new Response(JSON.stringify({ data: JSON.parse(fromCache) }), {
                    statusText: 'OK',
                    status: 200,
                    headers: { 'Content-Type': 'application/json' },
                });
                return Promise.resolve(response);
            }

            const result = await fetch(input, init);

            const res = await result.json();

            if (res.data !== undefined) {
                await redisClient.set(hash, JSON.stringify(res.data), { EX: ttl });
            }
            return Promise.resolve(
                new Response(JSON.stringify(res), {
                    statusText: 'OK',
                    status: 200,
                    headers: { 'Content-Type': 'application/json' },
                }),
            );
        } catch (e) {
            captureException(e);

            return fetch(input, init);
        }
    };
