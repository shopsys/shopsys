import { StateCreator } from 'zustand';

export type UserSlice = {
    cartUuid: string | null;
    productsComparisonUuid: string | null;

    updateUserState: (value: Partial<UserSlice>) => void;
};

export const createUserSlice: StateCreator<UserSlice> = (set) => ({
    cartUuid: null,
    productsComparisonUuid: null,

    updateUserState: (value) => {
        set(value);
    },
});
