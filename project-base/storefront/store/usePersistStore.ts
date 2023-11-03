import { AuthLoadingSlice, createAuthLoadingSlice } from './slices/createAuthLoadingSlice';
import { ContactInformationSlice, createContactInformationSlice } from './slices/createContactInformationSlice';
import { PacketerySlice, createPacketerySlice } from './slices/createPacketerySlice';
import { UserConsentSlice, createUserConsentSlice } from './slices/createUserConsentSlice';
import { createUserSlice, UserSlice } from './slices/createUserSlice';
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

type PersistStore = AuthLoadingSlice & UserSlice & ContactInformationSlice & PacketerySlice & UserConsentSlice;

export const usePersistStore = create<PersistStore>()(
    persist(
        (...store) => ({
            ...createAuthLoadingSlice(...store),
            ...createUserSlice(...store),
            ...createContactInformationSlice(...store),
            ...createPacketerySlice(...store),
            ...createUserConsentSlice(...store),
        }),
        { name: 'app-store' },
    ),
);
