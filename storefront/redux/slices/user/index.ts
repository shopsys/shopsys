import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { HYDRATE } from 'next-redux-wrapper';

export type PaginationType = {
    currentPage: number;
    paginationCursor: string;
    pageSize: number;
};

type InitialState = {
    pagination: PaginationType;
    canAccessOrderConfirmation: boolean;
    lastOrderUuid: string;
    lastOrderPaymentType: string;
    urlHash: string | undefined;
    cartUuid: string | null;
};

export const initialState = {
    pagination: {
        currentPage: 1,
        paginationCursor: '',
        pageSize: 9,
    },
    canAccessOrderConfirmation: false,
    urlHash: undefined,
    cartUuid: null,
} as InitialState;

export const userSlice = createSlice({
    name: 'user',
    initialState,
    reducers: {
        setPagination(state, action: PayloadAction<PaginationType>) {
            state.pagination = action.payload;
        },
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
