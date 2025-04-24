import { createFilterPanelSlice, FilterPanelSlice } from './slices/createFilterPanelSlice';
import { createGeolocationSlice, GeolocationSlice } from './slices/createGeolocationSlice';
import { createPageLoadingStateSlice, PageLoadingStateSlice } from './slices/createPageLoadingStateSlice';
import { createPortalSlice, PortalSlice } from './slices/createPortalSlice';
import { createSeoCategorySlice, SeoCategorySlice } from './slices/createSeoCategorySlice';
import { UserMenuSlice, createUserMenuSlice } from './slices/createUserMenuSlice';
import { create } from 'zustand';

type SessionStore = SeoCategorySlice &
    PageLoadingStateSlice &
    PortalSlice &
    FilterPanelSlice &
    GeolocationSlice &
    UserMenuSlice;

export const useSessionStore = create<SessionStore>()((...store) => ({
    ...createSeoCategorySlice(...store),
    ...createPageLoadingStateSlice(...store),
    ...createPortalSlice(...store),
    ...createFilterPanelSlice(...store),
    ...createGeolocationSlice(...store),
    ...createUserMenuSlice(...store),
}));
