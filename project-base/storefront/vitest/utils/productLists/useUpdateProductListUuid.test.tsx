import { act, renderHook } from '@testing-library/react';
import { TypeProductListTypeEnum } from 'graphql/types';
import { createUserSlice, defaultUserState, UserSlice } from 'store/slices/createUserSlice';
import { useUpdateProductListUuid } from 'utils/productLists/useUpdateProductListUuid';
import { beforeEach, expect, test, vi } from 'vitest';
import { create, useStore } from 'zustand';

const userStore = create<UserSlice>()(createUserSlice);
vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: UserSlice) => unknown) => useStore(userStore, selector),
    usePersistStoreApi: () => userStore,
}));
beforeEach(() => userStore.setState({ ...defaultUserState, productListUuids: { WISHLIST: 'original' } }));

test('preserves changes to both list identities made before the next render', () => {
    const { result } = renderHook(() => ({
        wishlist: useUpdateProductListUuid(TypeProductListTypeEnum.Wishlist),
        comparison: useUpdateProductListUuid(TypeProductListTypeEnum.Comparison),
    }));
    act(() => {
        result.current.wishlist('new-wishlist');
        result.current.comparison('new-comparison');
    });
    expect(userStore.getState().productListUuids).toEqual({ WISHLIST: 'new-wishlist', COMPARISON: 'new-comparison' });
});
