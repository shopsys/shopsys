import { createDomainSlice, DomainSlice } from './slices/createDomainSlice';
import { create } from 'zustand';
import { createSeoCategorySlice, SeoCategorySlice } from './slices/createSeoCategorySlice';

type SessionStore = DomainSlice & SeoCategorySlice;

export const useSessionStore = create<SessionStore>()((...store) => ({
    ...createDomainSlice(...store),
    ...createSeoCategorySlice(...store),
}));
