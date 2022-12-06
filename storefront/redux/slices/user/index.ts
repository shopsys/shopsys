import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { HYDRATE } from 'next-redux-wrapper';

export type PaginationType = {
    currentPage: number;
    paginationCursor: string;
    pageSize: number;
};

type LoginLoadingStatus = 'not-loading' | 'loading' | 'loading-with-cart-modifications';

type InitialState = {
    canAccessOrderConfirmation: boolean;
    lastOrderUuid: string;
    lastOrderPaymentType: string;
    urlHash: string | undefined;
    cartUuid: string | null;
    loginLoading: LoginLoadingStatus;
};

export const initialState = {
    canAccessOrderConfirmation: false,
    urlHash: undefined,
    cartUuid: null,
    loginLoading: 'not-loading',
} as InitialState;

export const userSlice = createSlice({
    name: 'user',
    initialState,
    reducers: {
        setOrderConfirmationAccess(state, action: PayloadAction<boolean>) {
            state.canAccessOrderConfirmation = action.payload;
        },
        setLastOrderUuid(state, action: PayloadAction<string>) {
            state.lastOrderUuid = action.payload;
        },
        setLastOrderPaymentType(state, action: PayloadAction<string>) {
            state.lastOrderPaymentType = action.payload;
        },
        setOrderUrlHash(state, action: PayloadAction<string | undefined>) {
            state.urlHash = action.payload;
        },
        setCartUuid(state, action: PayloadAction<string | null>) {
            state.cartUuid = action.payload;
        },
        setLoginLoading(state, action: PayloadAction<LoginLoadingStatus>) {
            state.loginLoading = action.payload;
        },
    },
    extraReducers: {
        /**
         * @see https://github.com/kirill-konshin/next-redux-wrapper#usage
         */
        [HYDRATE]: (state, action) => {
            return {
                ...state,
                ...action.payload.user,
            };
        },
    },
});

export const userActions = userSlice.actions;
