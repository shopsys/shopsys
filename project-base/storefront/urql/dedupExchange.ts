import { Exchange, Operation, OperationResult } from 'urql';
import { filter, pipe, tap } from 'wonka';

export const dedupExchange: Exchange = ({ forward, dispatchDebug }) => {
    const inFlightKeys = new Set<number>();

    const filterIncomingOperation = (operation: Operation) => {
        const { key, kind } = operation;
        if (kind === 'teardown') {
            inFlightKeys.delete(key);
            return true;
        }

        if (kind !== 'query' && kind !== 'subscription' && !operation.context.additionalTypenames?.includes('dedup')) {
            return true;
        }

        const isInFlight = inFlightKeys.has(key);
        inFlightKeys.add(key);

        if (isInFlight) {
            dispatchDebug({
                type: 'dedup',
                message: 'An operation has been deduped.',
                operation,
            });
        }

        return !isInFlight;
    };

    const afterOperationResult = ({ operation, hasNext }: OperationResult) => {
        if (!hasNext) {
            inFlightKeys.delete(operation.key);
        }
    };

    return (ops$) => {
        const forward$ = pipe(ops$, filter(filterIncomingOperation));
        return pipe(forward(forward$), tap(afterOperationResult));
    };
};
