import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { StoreType, TransportType } from 'connectors/transports/types';
import { CartType } from 'connectors/cart/types';
import { PaymentType } from 'connectors/payments/types';

const sortPriority = 'PRIORITY';
const sortPriceAsc = 'PRICE_ASC';
const sortPriceDesc = 'PRICE_DESC';
export const enabledSortTypes = [sortPriority, sortPriceAsc, sortPriceDesc];

export type SortType = typeof sortPriority | typeof sortPriceAsc | typeof sortPriceDesc;

export type PaginationType = {
    currentPage: number;
    paginationCursor: string;
};

type InitialState = {
    email: string | null;
    sort: SortType;
    cart: CartType | null;
    transport: TransportType | null;
    payment: PaymentType | null;
    personalPickupStore: StoreType | null;
    pagination: PaginationType;
};

export const initialState = {
    email: null,
    sort: sortPriority,
    cart: null,
    transport: null,
    payment: null,
    personalPickupStore: null,
    pagination: {
        currentPage: 1,
        paginationCursor: '',
    },
} as InitialState;

export type PayloadType = { sort: SortType };

export const userSlice = createSlice({
    name: 'user',
    initialState,
    reducers: {
        setEmail(state, action: PayloadAction<string | null>) {
            state.email = action.payload;
        },
        setSort(state, action: PayloadAction<PayloadType>) {
            state.sort = action.payload.sort;
        },
        setCart(state, action: PayloadAction<CartType | null>) {
            state.cart = action.payload;
        },
        setTransport(state, action: PayloadAction<TransportType | null>) {
            state.transport = action.payload;
        },
        setPersonalPickupStore(state, action: PayloadAction<StoreType | null>) {
            state.personalPickupStore = action.payload;
        },
        setPayment(state, action: PayloadAction<PaymentType | null>) {
            state.payment = action.payload;
        },
        setPagination(state, action: PayloadAction<PaginationType>) {
            state.pagination = action.payload;
        },
    },
});

export const userActions = userSlice.actions;
