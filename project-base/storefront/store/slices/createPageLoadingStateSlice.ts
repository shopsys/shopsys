import { FriendlyPagesTypesKey } from 'types/friendlyUrl';
import { StateCreator } from 'zustand';

type CUSTOM_PAGE_TYPES = [
    'cart',
    'comparison',
    'contact-information',
    'reset-password',
    'homepage',
    'order-confirmation',
    'orderDetail',
    'orderList',
    'complaintNew',
    'complaintDetail',
    'complaintList',
    'editProfile',
    'changePassword',
    'account',
    'productMainVariant',
    'registration',
    'stores',
    'transport-and-payment',
    'contact-information',
    'cart',
    'order-confirmation',
    'contact',
    'wishlist',
    'customer-users',
    'user-consent',
    'order-withdrawal',
    'order-withdrawal-success',
];

export type PageType = FriendlyPagesTypesKey | CUSTOM_PAGE_TYPES[number];

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
