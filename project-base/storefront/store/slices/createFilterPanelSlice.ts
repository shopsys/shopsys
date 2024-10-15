import { StateCreator } from 'zustand';

export type FilterPanelSlice = {
    isFilterPanelOpen: boolean;
    setIsFilterPanelOpen: (value: boolean) => void;
};

export const createFilterPanelSlice: StateCreator<FilterPanelSlice> = (set) => ({
    isFilterPanelOpen: false,

    setIsFilterPanelOpen: (value) => {
        set(() => ({
            isFilterPanelOpen: value,
        }));
    },
});
