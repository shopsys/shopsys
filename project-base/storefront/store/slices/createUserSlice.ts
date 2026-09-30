import { TypeLoginTypeEnum, TypeProductListTypeEnum } from 'graphql/types';
import { UserConsentFormType } from 'types/form';
import { StateCreator } from 'zustand';

type UserEntryType = 'login' | 'registration';

export type LastLoginType = Exclude<TypeLoginTypeEnum, TypeLoginTypeEnum.Admin>;

type ProductListStoreValue = Partial<{
    [key in TypeProductListTypeEnum]: string;
}>;

type UserState = {
    cartUuid: string | null;
    lastLoginType: LastLoginType | null;
    productListUuids: ProductListStoreValue;
    userConsent: UserConsentFormType | null;
    userEntry: UserEntryType | null;
};

export type UserSlice = UserState & {
    updateCartUuid: (value: string | null) => void;
    updateLastLoginType: (value: LastLoginType) => void;
    updateProductListUuids: (value: ProductListStoreValue) => void;
    updateUserConsent: (userConsent: UserConsentFormType) => void;
    updateUserEntryState: (value: UserEntryType | null) => void;
};

export const defaultUserState: UserState = {
    cartUuid: null,
    lastLoginType: null,
    productListUuids: {},
    userConsent: null,
    userEntry: null,
};

export const createUserSlice: StateCreator<UserSlice> = (set) => ({
    ...defaultUserState,

    updateCartUuid: (cartUuid) => {
        set({ cartUuid });
    },
    updateLastLoginType: (lastLoginType) => {
        set({ lastLoginType });
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
