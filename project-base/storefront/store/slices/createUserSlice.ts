import { TypeProductListTypeEnum } from 'graphql/types';
import { UserConsentFormType } from 'types/form';
import { StateCreator } from 'zustand';

type ProductListStoreValue = Partial<{
    [key in TypeProductListTypeEnum]: string;
}>;

type UserState = {
    cartUuid: string | null;
    productListUuids: ProductListStoreValue;
    userConsent: UserConsentFormType | null;
};

export type UserSlice = UserState & {
    updateCartUuid: (value: string | null) => void;
    updateProductListUuids: (value: ProductListStoreValue) => void;
    updateUserConsent: (userConsent: UserConsentFormType) => void;
};

export const defaultUserState: UserState = {
    cartUuid: null,
    productListUuids: {},
    userConsent: null,
};

export const createUserSlice: StateCreator<UserSlice> = (set) => ({
    ...defaultUserState,

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
