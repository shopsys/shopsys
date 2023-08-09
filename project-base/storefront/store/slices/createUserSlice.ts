import { StateCreator } from 'zustand';

export type UserSlice = {
    cartUuid: string | null;
    comparisonUuid: string | null;

    updateUserState: (value: Partial<UserSlice>) => void;
};

export const createUserSlice: StateCreator<UserSlice> = (set) => ({
    cartUuid: null,
    comparisonUuid: null,

    updateUserState: (value) => {
        set(value);
    },
});
