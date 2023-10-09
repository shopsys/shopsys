import { createDomainSlice, DomainSlice } from './slices/createDomainSlice';
import { createPageLoadingStateSlice, PageLoadingStateSlice } from './slices/createPageLoadingStateSlice';
import { createSeoCategorySlice, SeoCategorySlice } from './slices/createSeoCategorySlice';
import { create } from 'zustand';

type SessionStore = DomainSlice & SeoCategorySlice & PageLoadingStateSlice;

export const useSessionStore = create<SessionStore>()((...store) => ({
    ...createDomainSlice(...store),
    ...createSeoCategorySlice(...store),
    ...createPageLoadingStateSlice(...store),
}));
