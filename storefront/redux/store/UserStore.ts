import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { CartType } from 'connectors/cart/types';
import { HYDRATE } from 'next-redux-wrapper';

const sortPriority = 'PRIORITY';
const sortPriceAsc = 'PRICE_ASC';
const sortPriceDesc = 'PRICE_DESC';
export const enabledSortTypes = [sortPriority, sortPriceAsc, sortPriceDesc];

export type SortType = typeof sortPriority | typeof sortPriceAsc | typeof sortPriceDesc;

type IinitialState = {
    sort: SortType;
    cart: CartType | undefined;
};

export const initialState = {
    sort: sortPriority,
    cart: undefined,
} as IinitialState;

export type PayloadType = { sort: SortType };

export const userSlice = createSlice({
    name: 'user',
    initialState,
    reducers: {
        setSort(state, action: PayloadAction<PayloadType>) {
            state.sort = action.payload.sort;
        },
        setCart(state, action: PayloadAction<CartType | undefined>) {
            state.cart = action.payload;
        },
    },
    extraReducers: {
        [HYDRATE]: (state, action) => {
            return {
                ...state,
                ...action.payload.user,
            };
        },
    },
});

export const userActions = userSlice.actions;
