import { createDomainSlice, DomainSlice } from './slices/createDomainSlice';
import { create } from 'zustand';

type SessionStore = DomainSlice;

export const useSessionStore = create<SessionStore>()((...store) => ({
    ...createDomainSlice(...store),
}));
