import { StateCreator } from 'zustand';

export type PortalSlice = {
    portalContent: JSX.Element | null;

    updatePortalContent: (updatedPopupContent: JSX.Element | null) => void;
};

export const createPortalSlice: StateCreator<PortalSlice> = (set) => ({
    portalContent: null,

    updatePortalContent: (updatedPortalContent) => {
        set(() => ({
            portalContent: updatedPortalContent,
        }));
    },
});
