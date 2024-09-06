import { StateCreator } from 'zustand';
import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();
const userSnapEnabledDefaultValue = publicRuntimeConfig.userSnapEnabledDefaultValue;

export type UserSnapState = {
    isUserSnapEnabled: boolean;
};

export type UserSnapSlice = UserSnapState & {
    updateUserSnapState: (value: Partial<UserSnapSlice>) => void;
};

export const defaultUserSnapState = {
    isUserSnapEnabled: userSnapEnabledDefaultValue,
};

export const createUserSnapSlice: StateCreator<UserSnapSlice> = (set) => ({
    ...defaultUserSnapState,

    updateUserSnapState: (value) => {
        set(value);
    },
});
