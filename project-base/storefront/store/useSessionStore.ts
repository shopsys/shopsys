import { createDomainSlice, DomainSlice } from './slices/createDomainSlice';
import { createSeoCategorySlice, SeoCategorySlice } from './slices/createSeoCategorySlice';
import { create } from 'zustand';

type SessionStore = DomainSlice & SeoCategorySlice;

export const useSessionStore = create<SessionStore>()((...store) => ({
    ...createDomainSlice(...store),
    ...createSeoCategorySlice(...store),
}));
