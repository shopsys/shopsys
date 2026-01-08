import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { SkeletonEnum } from 'types/skeletons';
import { StateCreator } from 'zustand';

export type PageType = FriendlyPagesTypesKey | `${SkeletonEnum}`;

export type PageLoadingStateSlice = {
    hadClientSideNavigation: boolean;
    isCartHydrated: boolean;
    isPageLoading: boolean;
    isProductListHydrated: boolean;
    redirectPageType: PageType | undefined;

    updatePageLoadingState: (value: Partial<PageLoadingStateSlice>) => void;
};

export const createPageLoadingStateSlice: StateCreator<PageLoadingStateSlice> = (set) => ({
    hadClientSideNavigation: false,
    isCartHydrated: false,
    isPageLoading: false,
    isProductListHydrated: false,
    redirectPageType: undefined,

    updatePageLoadingState: (value) => {
        set((s) => ({ ...s, ...value }));
    },
});
