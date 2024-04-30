import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { StateCreator } from 'zustand';

const CUSTOM_PAGE_TYPES = [
    'homepage',
    'stores',
    'wishlist',
    'comparison',
    'orders',
    'order',
    'productMainVariant',
] as const;

export type PageType = FriendlyPagesTypesKey | (typeof CUSTOM_PAGE_TYPES)[number];

export type PageLoadingStateSlice = {
    hadClientSideNavigation: boolean;
    isCartHydrated: boolean;
    isPageLoading: boolean;
    redirectPageType: PageType | undefined;

    updatePageLoadingState: (value: Partial<PageLoadingStateSlice>) => void;
};

export const createPageLoadingStateSlice: StateCreator<PageLoadingStateSlice> = (set) => ({
    hadClientSideNavigation: false,
    isCartHydrated: false,
    isPageLoading: false,
    redirectPageType: undefined,

    updatePageLoadingState: (value) => {
        set((s) => ({ ...s, ...value }));
    },
});
