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
import { useClient } from 'urql';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useProductListQueryResult } from './useProductListQueryResult';
import { useUpdateProductListUuid } from './useUpdateProductListUuid';

export const useProductListMetadata = (
    type: TypeProductListTypeEnum,
    accountKey: string,
    visible: boolean,
    isSessionActive: () => boolean,
) => {
    const client = useClient();
    const store = usePersistStoreApi();
    const uuid = usePersistStore((state) => state.productListUuids[type]) ?? null;
    const hydrated = useSessionStore((state) => state.isProductListHydrated);
    const updateLoading = useSessionStore((state) => state.updatePageLoadingState);
    const loggedIn = useIsUserLoggedIn();
    const updateUuid = useUpdateProductListUuid(type);
    const [mutating, setMutating] = useState(false);
    const identity = useRef({ key: `${accountKey}:${uuid ?? ''}`, uuid, epoch: 0 });
    const refreshPending = useRef(false);
    const refreshing = useRef(false);
    const queue = useRef(Promise.resolve());
    const pending = useRef(new Set<string | symbol>());
    const getSelection = () => {
        const storedUuid = store.getState().productListUuids[type] ?? null;
        const key = `${accountKey}:${storedUuid ?? ''}`;
        if (identity.current.key !== key)
            identity.current = { key, uuid: storedUuid, epoch: identity.current.epoch + 1 };
        return { ...identity.current };
    };
    const selection = getSelection();
    const isCurrentSelection = (selected: typeof selection) => {
        const current = getSelection();
        return isSessionActive() && current.key === selected.key && current.epoch === selected.epoch;
    };
    const adoptUuid = (list: TypeProductListSummaryFragment | null, selected: typeof selection) => {
        if (!isCurrentSelection(selected)) return;
        const nextUuid = list?.uuid ?? null;
        identity.current = { ...identity.current, key: `${accountKey}:${nextUuid ?? ''}`, uuid: nextUuid };
        updateUuid(nextUuid);
    };
    const eligible = hydrated && (!!uuid || loggedIn) && isSessionActive();
    const input = { type, uuid };
    const result = useProductListQueryResult<TypeProductListSummaryQuery, TypeProductListSummaryQueryVariables>({
        query: ProductListSummaryQueryDocument,
        variables: { input },
        pause: !eligible || !visible || mutating,
        selectionKey: selection.key,
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
            result.operation?.context.requestPolicy !== 'cache-only' &&
            summary !== undefined
        ) {
            adoptUuid(summary, selection);
        }
    }, [summary, result.fetching, result.stale, result.error, result.operation, mutating, selection.key]);

    const invalidate = () => {
        if (!eligible || !visible || mutating) return;
        if (result.fetching || refreshing.current) {
            refreshPending.current = true;
            return;
        }
        refreshing.current = true;
        result.reexecute({ requestPolicy: 'network-only' });
    };
    useEffect(() => {
        if (!result.fetching) refreshing.current = false;
        if (eligible && visible && !mutating && !result.fetching && refreshPending.current) {
            refreshPending.current = false;
            refreshing.current = true;
            result.reexecute({ requestPolicy: 'network-only' });
        }
    }, [eligible, visible, mutating, result.fetching, result.operation, result.reexecute]);

    return {
        input,
        selectionKey: selection.key,
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
        applyMutationResult: adoptUuid,
        enqueueMutation: <T>(key: string | symbol, task: () => Promise<T>): Promise<T | undefined> | null => {
            if (pending.current.has(key) || pending.current.has('clear')) return null;
            const epoch = selection.epoch;
            pending.current.add(key);
            setMutating(true);
            const operation = queue.current.then(() => {
                if (!isSessionActive() || getSelection().epoch !== epoch) return undefined;
                return task();
            });
            queue.current = operation.then(
                () => {},
                () => {},
            );
            return operation.finally(() => {
                pending.current.delete(key);
                if (isSessionActive()) setMutating(pending.current.size > 0);
            });
        },
        refreshForRecovery: async () => {
            const selected = getSelection();
            if (!isCurrentSelection(selected)) throw new Error('Product list selection changed.');
            const fresh = await client
                .query<TypeProductListSummaryQuery, TypeProductListSummaryQueryVariables>(
                    ProductListSummaryQueryDocument,
                    { input: { type, uuid: selected.uuid } },
                    { requestPolicy: 'network-only', productListSelection: selected.key, productListContent: '' },
                )
                .toPromise();
            if (!isCurrentSelection(selected)) throw new Error('Product list selection changed.');
            if (fresh.error || fresh.data?.productList === undefined) {
                throw fresh.error ?? new Error('Product list response is missing.');
            }
            adoptUuid(fresh.data.productList, selected);
            return fresh.data.productList;
        },
    };
};
