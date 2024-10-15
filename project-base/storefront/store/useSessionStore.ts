import { createFilterPanelSlice, FilterPanelSlice } from './slices/createFilterPanelSlice';
import { createPageLoadingStateSlice, PageLoadingStateSlice } from './slices/createPageLoadingStateSlice';
import { createPortalSlice, PortalSlice } from './slices/createPortalSlice';
import { createSeoCategorySlice, SeoCategorySlice } from './slices/createSeoCategorySlice';
import { create } from 'zustand';

type SessionStore = SeoCategorySlice & PageLoadingStateSlice & PortalSlice & FilterPanelSlice;

export const useSessionStore = create<SessionStore>()((...store) => ({
    ...createSeoCategorySlice(...store),
    ...createPageLoadingStateSlice(...store),
    ...createPortalSlice(...store),
    ...createFilterPanelSlice(...store),
}));
