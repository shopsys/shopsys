import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { StateCreator } from 'zustand';

const CUSTOM_PAGE_TYPES = [
    'cart',
    'comparison',
    'contact-information',
    'homepage',
    'order-confirmation',
    'order',
    'orders',
    'productMainVariant',
    'registration',
    'stores',
    'transport-and-payment',
    'contact-information',
    'cart',
    'order-confirmation',
    'contact',
    'wishlist',
] as const;

export type PageType = FriendlyPagesTypesKey | (typeof CUSTOM_PAGE_TYPES)[number];

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
