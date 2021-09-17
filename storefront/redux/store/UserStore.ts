import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { CartType } from 'connectors/cart/types';

export type SortType = 'PRIORITY' | 'PRICE_ASC' | 'PRICE_DESC';

type IinitialState = {
    sort: SortType;
    cart: CartType | undefined;
};

const initialState = {
    sort: 'PRIORITY',
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
});

export const userActions = userSlice.actions;
