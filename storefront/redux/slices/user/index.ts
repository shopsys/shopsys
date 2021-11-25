import { createSlice, PayloadAction } from '@reduxjs/toolkit';
import { CartType } from 'connectors/cart/types';
import { HYDRATE } from 'next-redux-wrapper';
import { PaymentType } from 'connectors/payments/types';
import { PickupPlaceType } from 'connectors/transports/pickupPlace/types';
import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { TransportType } from 'connectors/transports/types';

export const enabledSortTypes = [
    ProductOrderingModeEnumApi.PriorityApi,
    ProductOrderingModeEnumApi.PriceAscApi,
    ProductOrderingModeEnumApi.PriceDescApi,
];

export type PaginationType = {
    currentPage: number;
    paginationCursor: string;
};

type InitialState = {
    sort: ProductOrderingModeEnumApi;
    cart: CartType | null;
    transport: TransportType | null;
    payment: PaymentType | null;
    pickupPlace: PickupPlaceType | null;
    pagination: PaginationType;
    canAccessOrderConfirmation: boolean;
};

export const initialState = {
    sort: ProductOrderingModeEnumApi.PriorityApi,
    cart: null,
    transport: null,
    payment: null,
    pickupPlace: null,
    pagination: {
        currentPage: 1,
        paginationCursor: '',
    },
    canAccessOrderConfirmation: false,
} as InitialState;

export const userSlice = createSlice({
    name: 'user',
    initialState,
    reducers: {
        setSort(state, action: PayloadAction<ProductOrderingModeEnumApi>) {
            state.sort = action.payload;
        },
        setCart(state, action: PayloadAction<CartType | null>) {
            state.cart = action.payload;
        },
        setTransport(state, action: PayloadAction<TransportType | null>) {
            state.transport = action.payload;
        },
        setPickupPlace(state, action: PayloadAction<PickupPlaceType | null>) {
            state.pickupPlace = action.payload;
        },
        setPayment(state, action: PayloadAction<PaymentType | null>) {
            state.payment = action.payload;
        },
        setPagination(state, action: PayloadAction<PaginationType>) {
            state.pagination = action.payload;
        },
        setOrderConfirmationAccess(state, action: PayloadAction<boolean>) {
            state.canAccessOrderConfirmation = action.payload;
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
