import { ExtendedLinkPageType } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { StateCreator } from 'zustand';

export type PageLoadingStateSlice = {
    isPageLoading: boolean;
    redirectPageType: ExtendedLinkPageType | undefined;

    updatePageLoadingState: (value: Partial<PageLoadingStateSlice>) => void;
};

export const createPageLoadingStateSlice: StateCreator<PageLoadingStateSlice> = (set) => ({
    isPageLoading: false,
    redirectPageType: undefined,

    updatePageLoadingState: (value) => {
        set((s) => ({ ...s, ...value }));
    },
});
