import { TypeProductListTypeEnum } from 'graphql/types';
import { UserConsentFormType } from 'types/form';
import { StateCreator } from 'zustand';

type ProductListStoreValue = Partial<{
    [key in TypeProductListTypeEnum]: string;
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
