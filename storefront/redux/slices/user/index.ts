import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { HYDRATE } from 'next-redux-wrapper';
import { ProductOrderingModeEnumApi } from 'graphql/generated';

export const enabledSortTypes = [
    ProductOrderingModeEnumApi.PriorityApi,
    ProductOrderingModeEnumApi.PriceAscApi,
    ProductOrderingModeEnumApi.PriceDescApi,
];

export type PaginationType = {
    currentPage: number;
    paginationCursor: string;
    pageSize: number;
};

type InitialState = {
    sort: ProductOrderingModeEnumApi;
    pagination: PaginationType;
    canAccessOrderConfirmation: boolean;
    isUserLoggedIn: boolean;
};

export const initialState = {
    sort: ProductOrderingModeEnumApi.PriorityApi,
    pagination: {
        currentPage: 1,
        paginationCursor: '',
        pageSize: 9,
    },
    canAccessOrderConfirmation: false,
    isUserLoggedIn: false,
} as InitialState;

export const userSlice = createSlice({
    name: 'user',
    initialState,
    reducers: {
        setSort(state, action: PayloadAction<ProductOrderingModeEnumApi>) {
            state.sort = action.payload;
        },
        setPagination(state, action: PayloadAction<PaginationType>) {
            state.pagination = action.payload;
        },
        setOrderConfirmationAccess(state, action: PayloadAction<boolean>) {
            state.canAccessOrderConfirmation = action.payload;
        },
        setIsUserLoggedIn(state, action: PayloadAction<boolean>) {
            state.isUserLoggedIn = action.payload;
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
