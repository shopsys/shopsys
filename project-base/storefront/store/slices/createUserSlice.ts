import { TypeProductListTypeEnum } from 'graphql/types';
import { UserConsentFormType } from 'types/form';
import { StateCreator } from 'zustand';

type UserEntryType = 'login' | 'registration';

type ProductListStoreValue = Partial<{
    [key in TypeProductListTypeEnum]: string;
}>;

type UserState = {
    comparisonSelection: { listUuid: string; productUuids: string[] } | null;
    cartUuid: string | null;
    productListUuids: ProductListStoreValue;
    userConsent: UserConsentFormType | null;
    userEntry: UserEntryType | null;
};

export type UserSlice = UserState & {
    updateComparisonSelection: (value: UserState['comparisonSelection']) => void;
    updateCartUuid: (value: string | null) => void;
    updateProductListUuids: (value: ProductListStoreValue) => void;
    updateUserConsent: (userConsent: UserConsentFormType) => void;
    updateUserEntryState: (value: UserEntryType | null) => void;
};

export const defaultUserState: UserState = {
    comparisonSelection: null,
    cartUuid: null,
    productListUuids: {},
    userConsent: null,
    userEntry: null,
};

export const createUserSlice: StateCreator<UserSlice> = (set) => ({
    ...defaultUserState,

    updateComparisonSelection: (comparisonSelection) => {
        set({ comparisonSelection });
    },
    updateCartUuid: (cartUuid) => {
        set({ cartUuid });
    },
    updateProductListUuids: (productListUuids) => {
        set({ productListUuids });
    },
    updateUserConsent: (userConsent) => {
        set({ userConsent });
    },
    updateUserEntryState: (value) => {
        set({ userEntry: value });
    },
});
