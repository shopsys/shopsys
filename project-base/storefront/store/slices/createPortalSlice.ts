import { StateCreator } from 'zustand';

export type PortalSlice = {
    portalContent: React.ReactNode | null;

    updatePortalContent: (updatedPopupContent: React.ReactNode | null) => void;
};

export const createPortalSlice: StateCreator<PortalSlice> = (set) => ({
    portalContent: null,

    updatePortalContent: (updatedPortalContent) => {
        set(() => ({
            portalContent: updatedPortalContent,
        }));
    },
});
