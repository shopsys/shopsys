import type { ServerResponse } from 'http';
import type { Exchange } from 'urql';
import { recordServerTiming } from 'utils/serverSide/serverTiming';
import { pipe, tap } from 'wonka';

export const getServerTimingExchange =
    (response: ServerResponse): Exchange =>
    ({ forward }) => {
        const pending = new Map<number, { startedAt: number; name: string }>();

        return (operations$) =>
            pipe(
                operations$,
                tap((operation) => {
                    if (operation.kind === 'teardown') {
                        pending.delete(operation.key);
                    } else if (operation.kind === 'query') {
                        const definition =
                            'definitions' in operation.query
                                ? operation.query.definitions.find(
                                      (definition) => definition.kind === 'OperationDefinition',
                                  )
                                : undefined;

                        pending.set(operation.key, {
                            startedAt: performance.now(),
                            name: definition?.name?.value ?? 'anonymous',
                        });
                    }
                }),
                forward,
                tap((result) => {
                    const timing = pending.get(result.operation.key);

                    if (timing && !result.hasNext) {
                        recordServerTiming(response, `gql_${timing.name}`, performance.now() - timing.startedAt);
                        pending.delete(result.operation.key);
                    }
                }),
            );
    };
