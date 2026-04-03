import { create } from 'zustand';
import { CartStaleSlice, createCartStaleSlice } from './slices/createCartStaleSlice';
import { createFilterPanelSlice, FilterPanelSlice } from './slices/createFilterPanelSlice';
import { createFocusManagementSlice, FocusManagementSlice } from './slices/createFocusManagementSlice';
import { createGeolocationSlice, GeolocationSlice } from './slices/createGeolocationSlice';
import { createNavigationOverflowSlise, NavigationOverflowSlice } from './slices/createNavigationOverflowSlise';
import { createPageLoadingStateSlice, PageLoadingStateSlice } from './slices/createPageLoadingStateSlice';
import { createPortalSlice, PortalSlice } from './slices/createPortalSlice';
import { createSeoCategorySlice, SeoCategorySlice } from './slices/createSeoCategorySlice';
import { createUserMenuSlice, UserMenuSlice } from './slices/createUserMenuSlice';

type SessionStore = CartStaleSlice &
    SeoCategorySlice &
    PageLoadingStateSlice &
    PortalSlice &
    FilterPanelSlice &
    GeolocationSlice &
    UserMenuSlice &
    NavigationOverflowSlice &
    FocusManagementSlice;

export const useSessionStore = create<SessionStore>()((...store) => ({
    ...createCartStaleSlice(...store),
    ...createSeoCategorySlice(...store),
    ...createPageLoadingStateSlice(...store),
    ...createPortalSlice(...store),
    ...createFilterPanelSlice(...store),
    ...createGeolocationSlice(...store),
    ...createUserMenuSlice(...store),
    ...createNavigationOverflowSlise(...store),
    ...createFocusManagementSlice(...store),
}));
