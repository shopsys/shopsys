import { useMemo, useRef } from 'react';
import { AnyVariables, createRequest, DocumentInput, useQuery } from 'urql';

export const useProductListQueryResult = <Data, Variables extends AnyVariables>({
    query,
    variables,
    pause,
    selectionKey,
    contentKey = '',
}: {
    query: DocumentInput<Data, Variables>;
    variables: Variables;
    pause: boolean;
    selectionKey: string;
    contentKey?: string;
}) => {
    const context = useMemo(
        () => ({ productListSelection: selectionKey, productListContent: contentKey }),
        [selectionKey, contentKey],
    );
    const [result, execute] = useQuery<Data, Variables>({
        query,
        variables,
        pause,
        requestPolicy: 'cache-and-network',
        context,
    });
    const requestKey = createRequest(query, variables).key;
    const current =
        result.operation?.key === requestKey &&
        result.operation.context.productListSelection === selectionKey &&
        result.operation.context.productListContent === contentKey;
    const previous = useRef<{ key: string; data: Data } | undefined>(undefined);
    const key = `${selectionKey}:${requestKey}`;

    // Keep a display fallback for failed refreshes; URQL remains the sole writer of server data.
    if (current && result.data !== undefined && !result.error && !result.stale && !result.fetching)
        previous.current = { key, data: result.data };

    return {
        ...result,
        reexecute: execute,
        data: current
            ? (result.error || result.stale) && previous.current?.key === key
                ? previous.current.data
                : result.data
            : undefined,
    };
};
