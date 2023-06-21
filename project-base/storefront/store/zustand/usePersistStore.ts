import { ContactInformationSlice, createContactInformationSlice } from './slices/createContactInformationSlice';
import { LoginLoadingSlice, createLoginLoadingSlice } from './slices/createGeneralSlice';
import { createUserSlice, UserSlice } from './slices/createUserSlice';
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

type PersistStore = LoginLoadingSlice & UserSlice & ContactInformationSlice;

export const usePersistStore = create<PersistStore>()(
    persist(
        (...store) => ({
            ...createLoginLoadingSlice(...store),
            ...createUserSlice(...store),
            ...createContactInformationSlice(...store),
        }),
        { name: 'app-store' },
    ),
);
