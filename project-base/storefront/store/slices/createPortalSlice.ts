import { ReactElement } from 'react';
import { StateCreator } from 'zustand';
import type { FocusManagementSlice } from './createFocusManagementSlice';

export type PortalSlice = {
    portalContent: ReactElement | null;

    updatePortalContent: (updatedPopupContent: ReactElement | null) => void;
    closePortalContent: () => void;
};

export const createPortalSlice: StateCreator<PortalSlice & FocusManagementSlice, [], [], PortalSlice> = (set, get) => ({
    portalContent: null,

    updatePortalContent: (updatedPortalContent) => {
        set(() => ({
            portalContent: updatedPortalContent,
        }));
    },

    closePortalContent: () => {
        set(() => ({
            portalContent: null,
        }));

        window.setTimeout(() => {
            get().restoreStoredFocus();
        });
    },
});
