import { broadcast } from './broadcast';
import { AuthLoadingSlice, createAuthLoadingSlice } from './slices/createAuthLoadingSlice';
import { ContactInformationSlice, createContactInformationSlice } from './slices/createContactInformationSlice';
import { PacketerySlice, createPacketerySlice } from './slices/createPacketerySlice';
import { createUserSlice, UserSlice } from './slices/createUserSlice';
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

type PersistStore = AuthLoadingSlice & UserSlice & ContactInformationSlice & PacketerySlice;

const STORE_NAME = 'app-store';

export const usePersistStore = create<PersistStore>()(
    persist(
        broadcast(
            (...store) => ({
                ...createAuthLoadingSlice(...store),
                ...createUserSlice(...store),
                ...createContactInformationSlice(...store),
                ...createPacketerySlice(...store),
            }),
            STORE_NAME,
        ),
        { name: STORE_NAME },
    ),
);
