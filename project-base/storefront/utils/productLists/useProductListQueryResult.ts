import { useEffect, useMemo, useRef } from 'react';
import { AnyVariables, createRequest, DocumentInput, RequestPolicy, useQuery } from 'urql';

export const useProductListQueryResult = <Data, Variables extends AnyVariables>({
    query,
    variables,
    pause,
    selectionKey,
    revision,
    requestPolicy = 'network-only',
}: {
    query: DocumentInput<Data, Variables>;
    variables: Variables;
    pause: boolean;
    selectionKey: string;
    revision: number;
    requestPolicy?: RequestPolicy;
}) => {
    const context = useMemo(() => ({ productListSelection: selectionKey }), [selectionKey]);
    // Read cached data synchronously; only the effect starts a refresh, including after unpausing.
    const [result, execute] = useQuery<Data, Variables>({
        query,
        variables,
        pause,
        requestPolicy: 'cache-only',
        context,
    });
    useEffect(() => {
        if (!pause) execute({ requestPolicy });
    }, [pause, revision, requestPolicy, execute]);
    const requestKey = createRequest(query, variables).key;
    const current =
        result.operation?.key === requestKey && result.operation.context.productListSelection === selectionKey;
    const previous = useRef<{ key: string; data: Data } | undefined>(undefined);
    const key = `${selectionKey}:${requestKey}`;

    // Keep a display fallback for failed refreshes; URQL remains the sole writer of server data.
    if (current && result.data !== undefined && !result.error) previous.current = { key, data: result.data };

    return {
        ...result,
        data: current
            ? (result.data ?? (result.error && previous.current?.key === key ? previous.current.data : undefined))
            : undefined,
    };
};
