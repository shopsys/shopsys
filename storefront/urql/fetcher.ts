import { RedisClientType } from '@node-redis/client';
import { captureException } from '@sentry/nextjs';
import md5 from 'crypto-js/md5';
import { isServer } from 'helpers/isServer';
import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();
const CACHE_REGEXP = `@redisCache\\(\\s?ttl:\\s?([0-9]*)\\s?\\)`;
const QUERY_NAME_REGEXP = `query\\s([A-z]*)(\\([A-z:!0-9$,\\s]*\\))?\\s@redisCache`;
const REDIS_URL = `redis://${process.env.REDIS_HOST}`;
const REDIS_PREFIX = `${process.env.REDIS_PREFIX}:fe:queryCache:`;

const removeDirectiveFromQuery = (query: string) => query.replace(new RegExp(CACHE_REGEXP), '');

const createInit = (init?: RequestInit | undefined) => ({
    ...init,
    body: typeof init?.body === 'string' ? removeDirectiveFromQuery(init.body) : init?.body,
});

export const fetcher =
    (redisClient: RedisClientType | null) =>
    async (input: RequestInfo, init?: RequestInit | undefined): Promise<Response> => {
        let client = redisClient;

        if (!isServer() || !init || publicRuntimeConfig.graphqlRedisCache !== '1') {
            return fetch(input, createInit(init));
        }

        try {
            if (typeof init.body !== 'string' || !init.body.match(CACHE_REGEXP)) {
                return fetch(input, createInit(init));
            }

            const [, rawTtl] = init.body.match(CACHE_REGEXP) as string[];
            const ttl = parseInt(rawTtl, 10);

            if (ttl <= 0) {
                return fetch(input, createInit(init));
            }

            const body = removeDirectiveFromQuery(init.body);
            const host = (init.headers ? new Headers(init.headers) : new Headers()).get('OriginalHost');
            const [, queryName] = init.body.match(QUERY_NAME_REGEXP) ?? [];
            const hash = `${REDIS_PREFIX}${host}:${queryName}:${md5(body).toString().substring(0, 7)}`;

            const createRedisClient = (await import('redis')).createClient;

            if (client === null) {
                client = createRedisClient({
                    url: REDIS_URL,
                    socket: {
                        connectTimeout: 5000,
                    },
                });

                await client.connect();
            }

            const fromCache = await client.get(hash);

            if (fromCache !== null) {
                const response = new Response(JSON.stringify({ data: JSON.parse(fromCache) }), {
                    statusText: 'OK',
                    status: 200,
                    headers: { 'Content-Type': 'text/html' },
                });

                return Promise.resolve(response);
            }

            const result = await fetch(input, {
                ...init,
                body,
            });

            const res = await result.json();

            if (res.data !== undefined) {
                await client.set(hash, JSON.stringify(res.data), { EX: ttl });
            }

            return Promise.resolve(
                new Response(JSON.stringify(res), {
                    statusText: 'OK',
                    status: 200,
                    headers: { 'Content-Type': 'text/html' },
                }),
            );
        } catch (e) {
            captureException(e);

            return fetch(input, createInit(init));
        }
    };
