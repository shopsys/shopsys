import { ContactInformationSlice, createContactInformationSlice } from './slices/createContactInformationSlice';
import { createUserSlice, UserSlice } from './slices/createUserSlice';
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

type PersistStore = UserSlice & ContactInformationSlice;

export const usePersistStore = create<PersistStore>()(
    persist(
        (...store) => ({
            ...createUserSlice(...store),
            ...createContactInformationSlice(...store),
        }),
        { name: 'app-store' },
    ),
);
