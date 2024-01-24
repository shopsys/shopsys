import { ProductListTypeEnumApi } from 'graphql/generated';
import { StateCreator } from 'zustand';

type ProductListStoreValue = Partial<{
    [key in ProductListTypeEnumApi]: string;
}>;

export type UserSlice = {
    cartUuid: string | null;
    productListUuids: ProductListStoreValue;
    userId: string | null;

    updateCartUuid: (value: string | null) => void;
    updateProductListUuids: (value: ProductListStoreValue) => void;
    updateUserId: (value: string | null) => void;
};

export const createUserSlice: StateCreator<UserSlice> = (set) => ({
    cartUuid: null,
    productListUuids: {},
    userId: null,

    updateCartUuid: (cartUuid) => {
        set({ cartUuid });
    },
    updateProductListUuids: (productListUuids) => {
        set({ productListUuids });
    },
    updateUserId: (userId) => {
        set({ userId });
    },
});
