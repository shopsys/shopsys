import { ContactInformationSlice, createContactInformationSlice } from './slices/createContactInformationSlice';
import { LoginLoadingSlice, createLoginLoadingSlice } from './slices/createGeneralSlice';
import { PacketerySlice, createPacketerySlice } from './slices/createPacketerySlice';
import { createUserSlice, UserSlice } from './slices/createUserSlice';
import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { UserConsentSlice, createUserConsentSlice } from './slices/createUserConsentSlice';
import { WishlistSlice, createWishlistSlice } from './slices/createWishlistSlice';

type PersistStore = LoginLoadingSlice &
    UserSlice &
    ContactInformationSlice &
    PacketerySlice &
    UserConsentSlice &
    WishlistSlice;

export const usePersistStore = create<PersistStore>()(
    persist(
        (...store) => ({
            ...createLoginLoadingSlice(...store),
            ...createUserSlice(...store),
            ...createContactInformationSlice(...store),
            ...createPacketerySlice(...store),
            ...createUserConsentSlice(...store),
            ...createWishlistSlice(...store),
        }),
        { name: 'app-store' },
    ),
);
