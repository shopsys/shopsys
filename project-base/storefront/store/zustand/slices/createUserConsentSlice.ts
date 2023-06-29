import { UserConsentFormType } from 'types/form';
import { StateCreator } from 'zustand';

export type UserConsentSlice = {
    userConsent: UserConsentFormType | null;

    setUserConsent: (userConsent: UserConsentFormType) => void;
    clearUserConsent: () => void;
};

export const createUserConsentSlice: StateCreator<UserConsentSlice> = (set) => ({
    userConsent: null,

    setUserConsent: (userConsent) => {
        set({ userConsent });
    },
    clearUserConsent: () => {
        set({ userConsent: null });
    },
});
