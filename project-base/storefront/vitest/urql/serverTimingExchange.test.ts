import { IncomingMessage, ServerResponse } from 'http';
import { Socket } from 'net';
// biome-ignore lint/style/noRestrictedImports: Isolated exchanges measure query timing without production networking.
import { createClient, type Exchange, gql, type OperationResult } from 'urql';
import { getServerTimingExchange } from 'urql/serverTimingExchange';
import { afterEach, describe, expect, test, vi } from 'vitest';
import { filter, fromPromise, mergeMap, pipe } from 'wonka';

describe('serverTimingExchange', () => {
    afterEach(() => {
        vi.unstubAllEnvs();
    });

    test('measures overlapping queries individually without publishing variables', async () => {
        vi.stubEnv('SERVER_TIMING', '1');
        const response = new ServerResponse(new IncomingMessage(new Socket()));
        const complete = new Map<string, () => void>();
        let now = 0;
        vi.spyOn(performance, 'now').mockImplementation(() => now);

        const terminalExchange: Exchange = () => (operations$) =>
            pipe(
                operations$,
                filter((operation) => operation.kind === 'query'),
                mergeMap((operation) =>
                    fromPromise(
                        new Promise<OperationResult>((resolve) => {
                            const definition =
                                'definitions' in operation.query
                                    ? operation.query.definitions.find(
                                          (definition) => definition.kind === 'OperationDefinition',
                                      )
                                    : undefined;
                            complete.set(definition!.name!.value, () =>
                                resolve({ operation, data: { value: true }, stale: false, hasNext: false }),
                            );
                        }),
                    ),
                ),
            );
        const client = createClient({
            url: 'https://example.com/graphql',
            exchanges: [getServerTimingExchange(response), terminalExchange],
        });

        const slow = client.query(gql`query Slow($userIdentifier: String!) { value }`, {
            userIdentifier: 'private-user',
        });
        const slowResult = slow.toPromise();
        now = 10;
        const fast = client.query(gql`query Fast { value }`, {});
        const fastResult = fast.toPromise();

        now = 30;
        complete.get('Fast')!();
        await fastResult;
        now = 100;
        complete.get('Slow')!();
        await slowResult;

        expect(response.getHeader('Server-Timing')).toBe('gql_Fast;dur=20.0, gql_Slow;dur=100.0');
    });
});
