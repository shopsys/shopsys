import { ProductListTypeEnumApi } from 'graphql/generated';
import { UserConsentFormType } from 'types/form';
import { StateCreator } from 'zustand';

type ProductListStoreValue = Partial<{
    [key in ProductListTypeEnumApi]: string;
}>;

export type UserSlice = {
    cartUuid: string | null;
    productListUuids: ProductListStoreValue;
    userConsent: UserConsentFormType | null;

    updateCartUuid: (value: string | null) => void;
    updateProductListUuids: (value: ProductListStoreValue) => void;
    updateUserConsent: (userConsent: UserConsentFormType) => void;
};

export const createUserSlice: StateCreator<UserSlice> = (set) => ({
    cartUuid: null,
    productListUuids: {},
    userConsent: null,

    updateCartUuid: (cartUuid) => {
        set({ cartUuid });
    },
    updateProductListUuids: (productListUuids) => {
        set({ productListUuids });
    },
    updateUserConsent: (userConsent) => {
        set({ userConsent });
    },
});
