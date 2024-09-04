import { StateCreator } from 'zustand';

export type UserSnapState = {
    isUserSnapEnabled: boolean;
};

export type UserSnapSlice = UserSnapState & {
    updateUserSnapState: (value: Partial<UserSnapSlice>) => void;
};

export const defaultUserSnapState = {
    isUserSnapEnabled: false,
};

export const createUserSnapSlice: StateCreator<UserSnapSlice> = (set) => ({
    ...defaultUserSnapState,

    updateUserSnapState: (value) => {
        set(value);
    },
});
