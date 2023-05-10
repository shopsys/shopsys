import { createDomainSlice, DomainSlice } from './slices/createDomainSlice';
import { createGeneralSlice, GeneralSlice } from './slices/createGeneralSlice';
import { create } from 'zustand';

type SessionStore = GeneralSlice & DomainSlice;

export const useSessionStore = create<SessionStore>()((...store) => ({
    ...createGeneralSlice(...store),
    ...createDomainSlice(...store),
}));
