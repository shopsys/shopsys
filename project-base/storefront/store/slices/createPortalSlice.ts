import { ReactElement } from 'react';
import { StateCreator } from 'zustand';

export type PortalSlice = {
    portalContent: ReactElement | null;

    updatePortalContent: (updatedPopupContent: ReactElement | null) => void;
};

export const createPortalSlice: StateCreator<PortalSlice> = (set) => ({
    portalContent: null,

    updatePortalContent: (updatedPortalContent) => {
        set(() => ({
            portalContent: updatedPortalContent,
        }));
    },
});
