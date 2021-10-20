import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { CartType } from 'connectors/cart/types';

const sortPriority = 'PRIORITY';
const sortPriceAsc = 'PRICE_ASC';
const sortPriceDesc = 'PRICE_DESC';
export const enabledSortTypes = [sortPriority, sortPriceAsc, sortPriceDesc];

export type SortType = typeof sortPriority | typeof sortPriceAsc | typeof sortPriceDesc;

export type PaginationType = {
    currentPage: number;
    paginationCursor: string;
};

type IinitialState = {
    sort: SortType;
    cart: CartType | undefined;
    pagination: PaginationType;
};

export const initialState = {
    sort: sortPriority,
    cart: undefined,
    pagination: {
        currentPage: 1,
        paginationCursor: '',
    },
} as IinitialState;

export type PayloadType = { sort: SortType };

export const userSlice = createSlice({
    name: 'user',
    initialState,
    reducers: {
        setSort(state, action: PayloadAction<PayloadType>) {
            state.sort = action.payload.sort;
        },
        setCart(state, action: PayloadAction<CartType>) {
            state.cart = action.payload;
            localStorage.setItem('cartUuid', action.payload.uuid);
        },
        setPagination(state, action: PayloadAction<PaginationType>) {
            state.pagination = action.payload;
        },
    },
});

export const userActions = userSlice.actions;
