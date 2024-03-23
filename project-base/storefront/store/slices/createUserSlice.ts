import { ProductListTypeEnum } from 'graphql/types';
import { UserConsentFormType } from 'types/form';
import { StateCreator } from 'zustand';

type ProductListStoreValue = Partial<{
    [key in ProductListTypeEnum]: string;
}>;

export type UserSlice = {
    cartUuid: string | null;
    productListUuids: ProductListStoreValue;
    userId: string | null;
    userConsent: UserConsentFormType | null;

    updateCartUuid: (value: string | null) => void;
    updateProductListUuids: (value: ProductListStoreValue) => void;
    updateUserId: (value: string | null) => void;
    updateUserConsent: (userConsent: UserConsentFormType) => void;
};

export const createUserSlice: StateCreator<UserSlice> = (set) => ({
    cartUuid: null,
    productListUuids: {},
    userId: null,
    userConsent: null,

    updateCartUuid: (cartUuid) => {
        set({ cartUuid });
    },
    updateProductListUuids: (productListUuids) => {
        set({ productListUuids });
    },
    updateUserId: (userId) => {
        set({ userId });
    },
    updateUserConsent: (userConsent) => {
        set({ userConsent });
    },
});
