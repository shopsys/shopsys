import { TypeProductListSummaryFragment } from 'graphql/requests/productLists/fragments/ProductListSummaryFragment.generated';
import {
    ProductListSummaryQueryDocument,
    TypeProductListSummaryQuery,
    TypeProductListSummaryQueryVariables,
} from 'graphql/requests/productLists/queries/ProductListSummaryQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { useEffect, useRef, useState } from 'react';
import { usePersistStore, usePersistStoreApi } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { RequestPolicy, useClient } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useProductListQueryResult } from './useProductListQueryResult';
import { useUpdateProductListUuid } from './useUpdateProductListUuid';
import { waitForVisiblePage } from './waitForVisiblePage';

export const useProductListMetadata = (
    type: TypeProductListTypeEnum,
    accountKey: string,
    visible: boolean,
    getSessionSignal: () => AbortSignal,
) => {
    const client = useClient();
    const store = usePersistStoreApi();
    const uuid = usePersistStore((state) => state.productListUuids[type]) ?? null;
    const hydrated = useSessionStore((state) => state.isProductListHydrated);
    const updateLoading = useSessionStore((state) => state.updatePageLoadingState);
    const loggedIn = useIsUserLoggedIn();
    const updateUuid = useUpdateProductListUuid(type);
    const [refresh, setRefresh] = useState({ revision: 0, requestPolicy: 'network-only' as RequestPolicy });
    const [mutating, setMutating] = useState(false);
    const identity = useRef({ key: `${accountKey}:${uuid ?? ''}`, uuid, epoch: 0 });
    const invalidationVersion = useRef(0);
    const queue = useRef(Promise.resolve());
    const pending = useRef(new Set<string | symbol>());
    const getSelection = () => {
        const storedUuid = store.getState().productListUuids[type] ?? null;
        const key = `${accountKey}:${storedUuid ?? ''}`;
        if (identity.current.key !== key)
            identity.current = { key, uuid: storedUuid, epoch: identity.current.epoch + 1 };
        return { ...identity.current, invalidationVersion: invalidationVersion.current };
    };
    const selection = getSelection();
    const isCurrentSelection = (selected: typeof selection) => {
        const current = getSelection();
        return !getSessionSignal().aborted && current.key === selected.key && current.epoch === selected.epoch;
    };
    const adoptUuid = (list: TypeProductListSummaryFragment | null, selected: typeof selection) => {
        if (!isCurrentSelection(selected)) return;
        const nextUuid = list?.uuid ?? null;
        identity.current = { ...identity.current, key: `${accountKey}:${nextUuid ?? ''}`, uuid: nextUuid };
        updateUuid(nextUuid);
    };
    const eligible = hydrated && (!!uuid || loggedIn) && !getSessionSignal().aborted;
    const input = { type, uuid };
    const result = useProductListQueryResult<TypeProductListSummaryQuery, TypeProductListSummaryQueryVariables>({
        query: ProductListSummaryQueryDocument,
        variables: { input },
        pause: !eligible || !visible || mutating,
        selectionKey: selection.key,
        ...refresh,
    });
    const received = result.data?.productList;
    const summary = eligible
        ? !uuid || received == null || received.uuid === uuid
            ? received
            : undefined
        : hydrated && !loggedIn && !uuid
          ? null
          : undefined;

    useEffect(() => updateLoading({ isProductListHydrated: true }), [updateLoading]);
    useEffect(() => {
        if (
            !mutating &&
            !result.fetching &&
            !result.stale &&
            !result.error &&
            result.operation?.context.requestPolicy === 'network-only' &&
            summary !== undefined
        ) {
            adoptUuid(summary, selection);
        }
    }, [summary, result.fetching, result.stale, result.error, result.operation, mutating, selection.key]);

    const invalidate = () => {
        invalidationVersion.current++;
        setRefresh((previous) => ({ revision: previous.revision + 1, requestPolicy: 'network-only' }));
    };
    const applyMutationResult = (list: TypeProductListSummaryFragment | null, selected: typeof selection) => {
        if (!isCurrentSelection(selected)) return;
        adoptUuid(list, selected);
        setRefresh((previous) => ({
            revision: previous.revision + 1,
            requestPolicy:
                invalidationVersion.current === selected.invalidationVersion ? 'cache-first' : 'network-only',
        }));
    };

    return {
        input,
        selectionKey: selection.key,
        revision: refresh.revision,
        summary,
        count: summary?.itemsCount ?? 0,
        hasMetadata: summary !== undefined,
        error: eligible && !!result.error,
        fetching: !hydrated || (eligible && (result.fetching || (summary === undefined && !result.error))),
        readyForDetails:
            eligible && !mutating && !result.fetching && !result.stale && !result.error && summary !== undefined,
        getSelection,
        isCurrentSelection,
        invalidate,
        applyMutationResult,
        enqueueMutation: <T>(key: string | symbol, task: () => Promise<T>): Promise<T | undefined> | null => {
            if (pending.current.has(key) || pending.current.has('clear')) return null;
            const epoch = selection.epoch;
            pending.current.add(key);
            setMutating(true);
            const operation = queue.current.then(() => {
                if (getSessionSignal().aborted || getSelection().epoch !== epoch) return undefined;
                return task();
            });
            queue.current = operation.then(
                () => {},
                () => {},
            );
            return operation.finally(() => {
                pending.current.delete(key);
                if (!getSessionSignal().aborted) setMutating(pending.current.size > 0);
            });
        },
        refreshForRecovery: async () => {
            const selected = getSelection();
            const signal = getSessionSignal();
            await waitForVisiblePage(signal);
            if (!isCurrentSelection(selected)) throw new Error('Product list selection changed.');
            const fresh = await client
                .query<TypeProductListSummaryQuery, TypeProductListSummaryQueryVariables>(
                    ProductListSummaryQueryDocument,
                    { input: { type, uuid: selected.uuid } },
                    { requestPolicy: 'network-only' },
                )
                .toPromise();
            if (!isCurrentSelection(selected)) throw new Error('Product list selection changed.');
            if (fresh.error || !fresh.data) throw fresh.error ?? new Error('Product list response is missing.');
            applyMutationResult(fresh.data.productList, selected);
            return fresh.data.productList;
        },
    };
};
